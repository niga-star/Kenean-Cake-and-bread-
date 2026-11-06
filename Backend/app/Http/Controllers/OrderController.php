<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Branch;
use App\Models\Product;
use App\Models\BranchInventory;
use App\Models\InventoryTransaction;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\DeliveryZone;
use App\Models\AppSetting;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * List orders scoped by role, branch, and status
     */
    public function index(Request $request)
    {
        self::releaseExpiredReservations();

        $user = $request->user();
        $query = Order::with(['branch', 'user', 'items.product', 'payment', 'statusHistory']);

        // Scoping rule: branch admin / staff see only their branch. Super Admin and Main Manager see all.
        if ($user && in_array($user->role, ['Branch Admin', 'Branch Staff']) && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->has('branch_id') && $request->branch_id && $request->branch_id !== 'all') {
            $query->where('branch_id', $request->branch_id);
        }

        // Customer scoping: see own account orders
        if ($user && $user->role === 'Customer') {
            $query->where('user_id', $user->user_id);
        }

        // Status filter
        if ($request->has('status') && $request->status && $request->status !== 'All') {
            $query->where('order_status', $request->status);
        }

        // Method filter
        if ($request->has('method') && $request->method && $request->method !== 'all') {
            $query->where('fulfillment_method', $request->method);
        }

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('phone', 'like', "%{$search}%")
                         ->orWhere('full_name', 'like', "%{$search}%");
                  });
            });
        }

        // Date filter
        if ($request->has('date') && $request->date === 'today') {
            $query->whereDate('placed_at', Carbon::today());
        }

        $orders = $query->latest('placed_at')->get();

        // Calculate pending count respecting branch scoping
        $countQuery = Order::query();
        if ($user && in_array($user->role, ['Branch Admin', 'Branch Staff']) && $user->branch_id) {
            $countQuery->where('branch_id', $user->branch_id);
        }
        $pendingCount = (clone $countQuery)->where('order_status', 'Pending')->count();

        return response()->json([
            'orders' => $orders,
            'pending_count' => $pendingCount,
        ]);
    }

    /**
     * Show single order by ID or order_number
     */
    public function show($id)
    {
        $order = Order::with(['branch', 'user', 'address', 'items.product', 'payments', 'payment', 'statusHistory.user'])
            ->where('order_id', $id)
            ->orWhere('order_number', $id)
            ->firstOrFail();

        $user = request()->user();
        if ($user && in_array($user->role, ['Branch Admin', 'Branch Staff']) && $user->branch_id && $order->branch_id != $user->branch_id) {
            return response()->json(['message' => 'Access denied: You can only view orders for your assigned branch.'], 403);
        }

        return response()->json($order);
    }

    /**
     * Create an order (Storefront checkout)
     * Rules:
     * - Every order belongs strictly to one selected branch.
     * - Stock availability checked at checkout inside transaction with row lock.
     * - An order cannot be placed for stock the branch does not have.
     * - Snapshot product name and unit price at time of purchase.
     * - Online payment orders start as Pending awaiting signed webhook.
     */
    public function store(Request $request)
    {
        self::releaseExpiredReservations();

        $user = $request->user();

        // Idempotency guard: Prevent duplicate order submissions
        $idempotencyKey = $request->header('Idempotency-Key') ?? $request->input('idempotency_key');
        if ($idempotencyKey) {
            $cachedOrderId = Cache::get("order_idempotency:{$idempotencyKey}");
            if ($cachedOrderId) {
                $existingOrder = Order::with(['branch', 'user', 'items.product', 'payment', 'statusHistory'])->find($cachedOrderId);
                if ($existingOrder) {
                    return response()->json($existingOrder, 200, [
                        'X-Idempotent-Replay' => 'true',
                    ]);
                }
            }
        }

        // Harmonize field aliases from storefront
        if (!$request->has('fulfillment_method') && $request->has('order_type')) {
            $request->merge(['fulfillment_method' => $request->order_type]);
        }
        if ($request->has('payment_method') || $request->has('payment_type')) {
            $rawPayment = $request->input('payment_method') ?? $request->input('payment_type');
            $mappedPayment = match ($rawPayment) {
                'Online', 'online', 'Chapa', 'chapa' => 'Chapa',
                'Telebirr', 'telebirr' => 'Telebirr',
                'Pickup', 'Pay on pickup', 'pay_on_pickup' => 'Pay on pickup',
                'Cash on Delivery', 'COD', 'cod', 'Pay on delivery', 'pay_on_delivery' => 'Pay on delivery',
                default => 'Pay on delivery',
            };
            $request->merge(['payment_method' => $mappedPayment]);
        }

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,branch_id',
            'fulfillment_method' => 'required|in:Delivery,Pickup',
            'address_id' => 'nullable|exists:addresses,address_id',
            'delivery_address' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:Chapa,Telebirr,Pay on pickup,Pay on delivery',
            'promo_code' => 'nullable|string|max:50',
            'promotion_code' => 'nullable|string|max:50',
        ]);

        $branch = Branch::findOrFail($validated['branch_id']);
        if ($branch->status !== 'Active') {
            return response()->json(['message' => 'Selected branch is currently inactive.'], 422);
        }

        // Auto-create or resolve address if delivery
        if ($validated['fulfillment_method'] === 'Delivery' && empty($validated['address_id'])) {
            $address = \App\Models\Address::create([
                'user_id' => $user?->user_id ?? 8,
                'city' => $request->city ?? 'Addis Ababa',
                'region' => $branch->branch_name,
                'street' => $request->delivery_address ?? 'Bole Road, Addis Ababa',
                'notes' => $request->order_notes ?? 'Deliver to doorstep',
                'is_default' => true,
            ]);
            $validated['address_id'] = $address->address_id;
        }

        $deliveryFee = 0.00;
        if ($validated['fulfillment_method'] === 'Delivery') {
            if ($request->filled('zone_id') || $request->filled('delivery_zone_id')) {
                $zoneId = $request->input('zone_id') ?? $request->input('delivery_zone_id');
                $zone = DeliveryZone::where('branch_id', $branch->branch_id)
                    ->where('zone_id', $zoneId)
                    ->where('status', 'Active')
                    ->first();
                if (!$zone) {
                    return response()->json(['message' => 'The selected delivery zone is not an active delivery zone for this branch.'], 422);
                }
                $deliveryFee = (float) $zone->delivery_fee;
            } else {
                $activeZone = DeliveryZone::where('branch_id', $branch->branch_id)->where('status', 'Active')->first();
                if (!$activeZone) {
                    return response()->json(['message' => "{$branch->branch_name} does not have active delivery zones. Please select Shop Pickup or another branch."], 422);
                }
                $deliveryFee = (float) $activeZone->delivery_fee;
            }
        }

        $order = DB::transaction(function () use ($validated, $branch, $deliveryFee, $user, $request) {
            // 1. Generate Order Number
            $rand = mt_rand(1000, 9999);
            $orderNumber = "ORD-{$rand}";
            while (Order::where('order_number', $orderNumber)->exists()) {
                $orderNumber = "ORD-" . mt_rand(1000, 9999);
            }

            // 2. Validate Stock Availability with Row Locks
            $subtotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];

                $inv = BranchInventory::where('branch_id', $branch->branch_id)
                    ->where('product_id', $product->product_id)
                    ->lockForUpdate()
                    ->first();

                $available = $inv ? max(0, $inv->quantity_on_hand - $inv->reserved_quantity) : 0;

                if ($available < $qty) {
                    abort(422, "Insufficient stock for {$product->product_name} at {$branch->branch_name}. Requested: {$qty}, Available: {$available}.");
                }

                // Reserve stock
                $inv->reserved_quantity += $qty;
                $inv->save();

                // Low stock alert check: notify branch admin and Main Manager if stock reaches reorder level
                if ($inv->available_quantity <= $inv->reorder_level) {
                    Notification::create([
                        'user_id' => null,
                        'category' => 'Stock',
                        'title' => 'Low stock',
                        'message' => "{$branch->branch_name} has {$inv->available_quantity} {$product->product_name} left (at or below reorder level {$inv->reorder_level}).",
                        'link' => '/admin/inventory',
                    ]);
                }

                // Snapshot price (branch override if exists, else base price)
                $unitPrice = $product->getPriceForBranch($branch->branch_id);
                $lineTotal = $unitPrice * $qty;
                $subtotal += $lineTotal;

                $itemsData[] = [
                    'product_id' => $product->product_id,
                    'product_name' => $product->product_name,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineTotal,
                ];

                InventoryTransaction::create([
                    'branch_id' => $branch->branch_id,
                    'product_id' => $product->product_id,
                    'transaction_type' => 'order_placed',
                    'quantity' => $qty,
                    'reference_type' => 'Order',
                    'reason' => "Stock reserved for order {$orderNumber}",
                    'created_by' => $user?->user_id,
                ]);
            }

            // Dynamic promotion code discount validation from MySQL promotions table
            $discountAmount = 0.00;
            $rawPromoCode = strtoupper(trim($request->input('promo_code') ?? $request->input('promotion_code') ?? ''));
            if (!empty($rawPromoCode)) {
                $promo = \App\Models\Promotion::whereRaw('UPPER(promotion_name) = ?', [$rawPromoCode])
                    ->where('status', 'Active')
                    ->first();
                if ($promo) {
                    $today = \Carbon\Carbon::today();
                    $isDateValid = (!$promo->start_date || \Carbon\Carbon::parse($promo->start_date)->startOfDay()->lte($today)) &&
                                   (!$promo->end_date || \Carbon\Carbon::parse($promo->end_date)->endOfDay()->gte($today));
                    if ($isDateValid) {
                        if ($promo->discount_type === 'percentage') {
                            $discountAmount = round(($subtotal * (float) $promo->discount_value) / 100, 2);
                        } else {
                            $discountAmount = min($subtotal, (float) $promo->discount_value);
                        }
                    }
                }
            }

            $totalAmount = max(0, ($subtotal - $discountAmount) + $deliveryFee);

            // Online payments start as Pending. Pay on pickup/delivery start as Confirmed.
            $isOnline = in_array($validated['payment_method'], ['Chapa', 'Telebirr']);
            $initialStatus = $isOnline ? 'Pending' : 'Confirmed';

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user?->user_id ?? 1,
                'branch_id' => $branch->branch_id,
                'address_id' => $validated['address_id'] ?? null,
                'fulfillment_method' => $validated['fulfillment_method'],
                'order_status' => $initialStatus,
                'delivery_fee' => $deliveryFee,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'placed_at' => now(),
            ]);

            foreach ($itemsData as $item) {
                OrderItem::create([
                    'order_id' => $order->order_id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            // Order status history
            OrderStatusHistory::create([
                'order_id' => $order->order_id,
                'old_status' => null,
                'new_status' => $initialStatus,
                'changed_by' => $user?->user_id,
                'note' => $isOnline ? 'Order placed, awaiting payment confirmation' : 'Order placed, accepted for pay-on-pickup/delivery',
                'changed_at' => now(),
            ]);

            // Payment record: Online payments start as Pending with gateway transaction reference
            $txPrefix = $validated['payment_method'] === 'Chapa' ? 'CH-' : ($validated['payment_method'] === 'Telebirr' ? 'TB-' : 'PAY-');
            $txRef = $isOnline ? ($txPrefix . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6))) : null;

            Payment::create([
                'order_id' => $order->order_id,
                'payment_method' => $validated['payment_method'],
                'amount' => $totalAmount,
                'payment_status' => 'Pending',
                'transaction_reference' => $txRef,
            ]);

            AuditLog::create([
                'user_id' => $user?->user_id,
                'user_name' => $user ? ($user->full_name . ' (' . $user->role . ')') : 'Customer',
                'action' => 'Order created',
                'record' => $orderNumber,
                'entity_type' => 'Order',
                'entity_id' => (string) $order->order_id,
                'old_value' => '—',
                'new_value' => $initialStatus . ' (ETB ' . number_format($totalAmount, 0) . ')',
                'created_at' => now(),
            ]);

            Notification::create([
                'user_id' => null,
                'category' => 'Orders',
                'title' => "New Order {$orderNumber}",
                'message' => "Order {$orderNumber} placed for {$branch->branch_name} (ETB " . number_format($totalAmount, 0) . ")",
                'link' => '/admin/orders',
            ]);

            return $order;
        });

        if ($idempotencyKey) {
            Cache::put("order_idempotency:{$idempotencyKey}", $order->order_id, now()->addMinutes(15));
        }

        return response()->json($order->load(['branch', 'items.product', 'payment']), 201);
    }

    /**
     * Update order status with strict lifecycle rules
     * Status Lifecycle:
     * - Pending -> Confirmed, Cancelled
     * - Confirmed -> Preparing, Cancelled
     * - Preparing -> Ready
     * - Ready -> Out for delivery (Delivery only), Completed (Pickup only)
     * - Out for delivery -> Completed
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::with(['items', 'payment', 'branch'])->findOrFail($id);
        $user = $request->user();

        // Branch scoping: branch admin and staff can only update their assigned branch
        if ($user && in_array($user->role, ['Branch Admin', 'Branch Staff']) && $user->branch_id && $order->branch_id != $user->branch_id) {
            return response()->json(['message' => 'Access denied: You can only manage orders for your assigned branch.'], 403);
        }

        if (!$request->has('order_status') && $request->has('status')) {
            $request->merge(['order_status' => $request->status]);
        }
        if (strcasecmp($request->order_status, 'Out for Delivery') === 0) {
            $request->merge(['order_status' => 'Out for delivery']);
        }

        $validated = $request->validate([
            'order_status' => 'required|in:Pending,Confirmed,Preparing,Ready,Out for delivery,Out for Delivery,Completed,Cancelled',
            'note' => 'nullable|string',
        ]);

        $oldStatus = $order->order_status;
        $newStatus = strcasecmp($validated['order_status'], 'Out for Delivery') === 0 ? 'Out for delivery' : $validated['order_status'];

        if ($oldStatus === $newStatus) {
            return response()->json($order);
        }

        // Rule: Terminal states cannot be changed
        if (in_array($oldStatus, ['Completed', 'Cancelled'])) {
            return response()->json(['message' => "Cannot change status of an order that is already {$oldStatus}."], 422);
        }

        // Rule: Out for delivery is applicable ONLY to delivery orders
        if ($newStatus === 'Out for delivery' && $order->fulfillment_method !== 'Delivery') {
            return response()->json(['message' => 'Out for delivery status is only valid for Delivery orders. Shop pickup orders transition from Ready directly to Completed.'], 422);
        }

        DB::transaction(function () use ($order, $oldStatus, $newStatus, $validated, $user) {
            // When order is Completed: physical stock drops and reserved stock is deducted
            if ($newStatus === 'Completed') {
                foreach ($order->items as $item) {
                    $inv = BranchInventory::where('branch_id', $order->branch_id)
                        ->where('product_id', $item->product_id)
                        ->lockForUpdate()
                        ->first();

                    if ($inv) {
                        $inv->quantity_on_hand = max(0, $inv->quantity_on_hand - $item->quantity);
                        $inv->reserved_quantity = max(0, $inv->reserved_quantity - $item->quantity);
                        $inv->save();
                    }

                    InventoryTransaction::create([
                        'branch_id' => $order->branch_id,
                        'product_id' => $item->product_id,
                        'transaction_type' => 'order_completed',
                        'quantity' => -$item->quantity,
                        'reference_type' => 'Order',
                        'reference_id' => $order->order_id,
                        'reason' => "Order {$order->order_number} completed, stock deducted",
                        'created_by' => $user?->user_id,
                    ]);
                }

                // If pay on pickup or pay on delivery, mark payment as Paid upon completion
                if ($order->payment && $order->payment->payment_status === 'Pending') {
                    $order->payment->payment_status = 'Paid';
                    $order->payment->save();
                }
            }

            // When order is Cancelled: release reserved stock
            if ($newStatus === 'Cancelled') {
                foreach ($order->items as $item) {
                    $inv = BranchInventory::where('branch_id', $order->branch_id)
                        ->where('product_id', $item->product_id)
                        ->lockForUpdate()
                        ->first();

                    if ($inv) {
                        $inv->reserved_quantity = max(0, $inv->reserved_quantity - $item->quantity);
                        $inv->save();
                    }

                    InventoryTransaction::create([
                        'branch_id' => $order->branch_id,
                        'product_id' => $item->product_id,
                        'transaction_type' => 'order_cancelled',
                        'quantity' => -$item->quantity,
                        'reference_type' => 'Order',
                        'reference_id' => $order->order_id,
                        'reason' => "Order {$order->order_number} cancelled, reservation released",
                        'created_by' => $user?->user_id,
                    ]);
                }

                // If payment was already Paid, mark Refunded
                if ($order->payment && $order->payment->payment_status === 'Paid') {
                    $order->payment->payment_status = 'Refunded';
                    $order->payment->refund_amount = $order->total_amount;
                    $order->payment->refund_reason = $validated['note'] ?? 'Cancelled by staff with refund';
                    $order->payment->save();
                }
            }

            $order->order_status = $newStatus;
            $order->save();

            // Write status history record
            OrderStatusHistory::create([
                'order_id' => $order->order_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $user?->user_id,
                'note' => $validated['note'] ?? "Status updated to {$newStatus}",
                'changed_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => $user?->user_id,
                'user_name' => $user ? ($user->full_name . ' (' . $user->role . ')') : 'System',
                'action' => 'Order status change',
                'record' => $order->order_number,
                'entity_type' => 'Order',
                'entity_id' => (string) $order->order_id,
                'old_value' => $oldStatus,
                'new_value' => $newStatus,
                'created_at' => now(),
            ]);
        });

        return response()->json($order->load(['items.product', 'payment', 'statusHistory']));
    }

    /**
     * Cancel an order (Customer or Staff)
     * Rule: Customers can cancel while Pending or Confirmed.
     * Cancellation is strictly blocked once preparation starts, except for branch-approved cases.
     */
    public function cancel(Request $request, $id)
    {
        $order = Order::with(['items', 'payment', 'branch'])->where('order_id', $id)->orWhere('order_number', $id)->firstOrFail();
        $user = $request->user();

        // Check if customer cancellation rule applies
        $isCustomer = (!$user || $user->role === 'Customer' || $order->user_id === $user?->user_id);

        if ($isCustomer && in_array($order->order_status, ['Preparing', 'Ready', 'Out for delivery', 'Completed'])) {
            return response()->json([
                'message' => 'Cancellation is blocked once preparation has started. Please contact the branch directly for assistance.'
            ], 422);
        }

        if (in_array($order->order_status, ['Completed', 'Cancelled'])) {
            return response()->json(['message' => "Order is already {$order->order_status}."], 422);
        }

        DB::transaction(function () use ($order, $request, $user) {
            $oldStatus = $order->order_status;

            // Release reserved stock
            foreach ($order->items as $item) {
                $inv = BranchInventory::where('branch_id', $order->branch_id)
                    ->where('product_id', $item->product_id)
                    ->lockForUpdate()
                    ->first();

                if ($inv) {
                    $inv->reserved_quantity = max(0, $inv->reserved_quantity - $item->quantity);
                    $inv->save();
                }

                InventoryTransaction::create([
                    'branch_id' => $order->branch_id,
                    'product_id' => $item->product_id,
                    'transaction_type' => 'order_cancelled',
                    'quantity' => -$item->quantity,
                    'reference_type' => 'Order',
                    'reference_id' => $order->order_id,
                    'reason' => "Order {$order->order_number} cancelled, reservation released",
                    'created_by' => $user?->user_id,
                ]);
            }

            // Refund if paid
            if ($order->payment && $order->payment->payment_status === 'Paid') {
                $order->payment->payment_status = 'Refunded';
                $order->payment->refund_amount = $order->total_amount;
                $order->payment->refund_reason = $request->reason ?? 'Cancelled before preparation';
                $order->payment->save();
            }

            $order->order_status = 'Cancelled';
            $order->save();

            OrderStatusHistory::create([
                'order_id' => $order->order_id,
                'old_status' => $oldStatus,
                'new_status' => 'Cancelled',
                'changed_by' => $user?->user_id,
                'note' => $request->reason ?? 'Cancelled by customer',
                'changed_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => $user?->user_id,
                'user_name' => $user ? ($user->full_name . ' (' . $user->role . ')') : 'Customer',
                'action' => 'Order cancelled',
                'record' => $order->order_number,
                'entity_type' => 'Order',
                'entity_id' => (string) $order->order_id,
                'old_value' => $oldStatus,
                'new_value' => 'Cancelled',
                'created_at' => now(),
            ]);

            Notification::create([
                'user_id' => null,
                'category' => 'Orders',
                'title' => 'Order cancelled',
                'message' => "Order {$order->order_number} for {$order->branch?->branch_name} was cancelled.",
                'link' => '/admin/orders',
            ]);
        });

        return response()->json([
            'message' => 'Order cancelled successfully and stock reservation released.',
            'order' => $order->fresh(['items.product', 'payment', 'statusHistory']),
        ]);
    }

    /**
     * Reassign branch (Super Admin only action)
     * Rule: The branch_id on an order is mandatory and cannot be changed after payment without Super Admin action.
     */
    public function reassignBranch(Request $request, $id)
    {
        $order = Order::with(['items', 'branch'])->findOrFail($id);
        $user = $request->user();

        $validated = $request->validate([
            'new_branch_id' => 'required|exists:branches,branch_id',
            'reason' => 'required|string|max:500',
        ]);

        $newBranch = Branch::findOrFail($validated['new_branch_id']);
        if ($newBranch->branch_id === $order->branch_id) {
            return response()->json(['message' => 'Order is already assigned to this branch.'], 422);
        }
        if ($newBranch->status !== 'Active') {
            return response()->json(['message' => 'Destination branch is inactive.'], 422);
        }

        DB::transaction(function () use ($order, $newBranch, $validated, $user) {
            $oldBranchName = $order->branch?->branch_name ?? "Branch #{$order->branch_id}";

            // Re-validate and shift reservation if order is not completed
            if ($order->order_status !== 'Completed' && $order->order_status !== 'Cancelled') {
                foreach ($order->items as $item) {
                    // Check availability at new branch
                    $newInv = BranchInventory::where('branch_id', $newBranch->branch_id)
                        ->where('product_id', $item->product_id)
                        ->lockForUpdate()
                        ->first();

                    $availableAtNew = $newInv ? max(0, $newInv->quantity_on_hand - $newInv->reserved_quantity) : 0;
                    if ($availableAtNew < $item->quantity) {
                        abort(422, "Cannot reassign to {$newBranch->branch_name}: insufficient available stock for {$item->product_name} (Requested: {$item->quantity}, Available: {$availableAtNew}).");
                    }

                    // Release at old branch
                    $oldInv = BranchInventory::where('branch_id', $order->branch_id)
                        ->where('product_id', $item->product_id)
                        ->lockForUpdate()
                        ->first();
                    if ($oldInv) {
                        $oldInv->reserved_quantity = max(0, $oldInv->reserved_quantity - $item->quantity);
                        $oldInv->save();
                    }

                    // Reserve at new branch
                    $newInv->reserved_quantity += $item->quantity;
                    $newInv->save();
                }
            }

            $order->branch_id = $newBranch->branch_id;
            $order->save();

            OrderStatusHistory::create([
                'order_id' => $order->order_id,
                'old_status' => $order->order_status,
                'new_status' => $order->order_status,
                'changed_by' => $user?->user_id,
                'note' => "Branch reassigned from {$oldBranchName} to {$newBranch->branch_name} by Super Admin: {$validated['reason']}",
                'changed_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => $user?->user_id,
                'user_name' => $user ? ($user->full_name . ' (' . $user->role . ')') : 'Super Admin',
                'action' => 'Order branch reassigned',
                'record' => $order->order_number,
                'entity_type' => 'Order',
                'entity_id' => (string) $order->order_id,
                'old_value' => $oldBranchName,
                'new_value' => $newBranch->branch_name,
                'created_at' => now(),
            ]);
        });

        return response()->json($order->fresh(['branch', 'items.product', 'payment', 'statusHistory']));
    }

    /**
     * Release expired reservations for unpaid online orders
     * Configurable duration (default 15 minutes)
     */
    public static function releaseExpiredReservations()
    {
        $timeoutMinutes = 15;
        $setting = AppSetting::where('key', 'unpaid_order_timeout_minutes')->first();
        if ($setting && is_numeric($setting->value)) {
            $timeoutMinutes = (int) $setting->value;
        }

        $cutoff = Carbon::now()->subMinutes($timeoutMinutes);

        $expiredOrders = Order::with('items')
            ->where('order_status', 'Pending')
            ->where('placed_at', '<=', $cutoff)
            ->whereHas('payments', function ($q) {
                $q->where('payment_status', 'Pending')
                  ->whereIn('payment_method', ['Chapa', 'Telebirr', 'Online', 'online']);
            })
            ->get();

        $releasedCount = 0;

        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order, $timeoutMinutes, &$releasedCount) {
                foreach ($order->items as $item) {
                    $inv = BranchInventory::where('branch_id', $order->branch_id)
                        ->where('product_id', $item->product_id)
                        ->lockForUpdate()
                        ->first();

                    if ($inv) {
                        $inv->reserved_quantity = max(0, $inv->reserved_quantity - $item->quantity);
                        $inv->save();
                    }

                    InventoryTransaction::create([
                        'branch_id' => $order->branch_id,
                        'product_id' => $item->product_id,
                        'transaction_type' => 'order_cancelled',
                        'quantity' => -$item->quantity,
                        'reference_type' => 'Order',
                        'reference_id' => $order->order_id,
                        'reason' => "Unpaid online order reservation timed out after {$timeoutMinutes} minutes",
                        'created_by' => null,
                    ]);
                }

                $order->order_status = 'Cancelled';
                $order->save();

                OrderStatusHistory::create([
                    'order_id' => $order->order_id,
                    'old_status' => 'Pending',
                    'new_status' => 'Cancelled',
                    'changed_by' => null,
                    'note' => "Reservation expired: payment not confirmed within {$timeoutMinutes} minutes",
                    'changed_at' => now(),
                ]);

                AuditLog::create([
                    'user_id' => null,
                    'user_name' => 'System',
                    'action' => 'Order reservation expired',
                    'record' => $order->order_number,
                    'entity_type' => 'Order',
                    'entity_id' => (string) $order->order_id,
                    'old_value' => 'Pending',
                    'new_value' => 'Cancelled',
                    'created_at' => now(),
                ]);

                $releasedCount++;
            });
        }

        return $releasedCount;
    }

    public function releaseExpiredEndpoint()
    {
        $count = self::releaseExpiredReservations();
        return response()->json([
            'message' => "Successfully released reservations for {$count} expired orders.",
            'released_count' => $count,
        ]);
    }
}
