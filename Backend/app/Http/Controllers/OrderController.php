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
use App\Models\User;
use App\Models\BranchInventory;
use App\Models\InventoryTransaction;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\CustomCakeRequest;
use App\Models\DeliveryZone;
use App\Models\AppSetting;
use Carbon\Carbon;
use App\Services\OrderService;
use App\Services\InventoryService;
use App\Http\Requests\StoreOrderRequest;

class OrderController extends Controller
{
    protected OrderService $orderService;
    protected InventoryService $inventoryService;

    public function __construct(OrderService $orderService, InventoryService $inventoryService)
    {
        $this->orderService = $orderService;
        $this->inventoryService = $inventoryService;
    }
    /**
     * List orders scoped by role, branch, and status
     */
    public function index(Request $request)
    {
        self::releaseExpiredReservations();

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'orders' => [],
                'pending_count' => 0,
            ]);
        }

        $userBranchId = $user?->branch_id ?? ($user ? Branch::where('manager_id', $user->user_id)->value('branch_id') : null);
        $query = Order::with(['branch', 'user', 'items.product', 'payment', 'statusHistory']);

        // Scoping rule: Super Admin does not manage branch operational orders; these belong exclusively to Branch Sellers and Branch Managers.
        if ($user && $user->role !== 'Customer') {
            if ($user->role === 'Super Admin') {
                return response()->json([
                    'orders' => [],
                    'pending_count' => 0,
                    'message' => 'Operational orders belong exclusively to Branch Sellers and Branch Managers.',
                ]);
            } elseif ($userBranchId) {
                // Non-super-admin is strictly scoped to own branch; cannot query other branches
                $query->where('branch_id', $userBranchId);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Customer scoping: see own account orders & verified email historical orders
        if ($user && $user->role === 'Customer') {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->user_id);
                if ($user->email_verified_at) {
                    $q->orWhereRaw('LOWER(customer_email) = ?', [strtolower(trim($user->email))]);
                } elseif (!empty($user->phone)) {
                    $q->orWhere('customer_phone', $user->phone);
                }
            });
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
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('phone', 'like', "%{$search}%")
                         ->orWhere('full_name', 'like', "%{$search}%");
                  });
            });
        }

        // Date filter (Step 6: Today, This Week, This Month, Custom Date Range, All Orders)
        $dateFilter = $request->query('date_range') ?: $request->query('date');
        if ($dateFilter && $dateFilter !== 'all') {
            $now = Carbon::now();
            if ($dateFilter === 'today') {
                $query->whereDate('placed_at', Carbon::today());
            } elseif ($dateFilter === 'this_week' || $dateFilter === 'week') {
                $query->whereBetween('placed_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
            } elseif ($dateFilter === 'this_month' || $dateFilter === 'month') {
                $query->whereBetween('placed_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
            } elseif ($dateFilter === 'custom') {
                $startDate = $request->query('start_date');
                $endDate = $request->query('end_date') ?: $startDate;
                if ($startDate) {
                    $query->whereDate('placed_at', '>=', $startDate)
                          ->whereDate('placed_at', '<=', $endDate);
                }
            }
        } elseif ($request->has('start_date') && $request->start_date) {
            $query->whereDate('placed_at', '>=', $request->start_date);
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('placed_at', '<=', $request->end_date);
            }
        }

        $orders = $query->latest('placed_at')->get();

        // Calculate pending count respecting branch scoping
        $countQuery = Order::query();
        if ($user && $user->role !== 'Super Admin' && $userBranchId) {
            $countQuery->where('branch_id', $userBranchId);
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
        if ($user && $user->role === 'Super Admin') {
            return response()->json(['message' => 'Operational order management belongs exclusively to Branch Sellers and Branch Managers.'], 403);
        }
        $userBranchId = $user?->branch_id ?? ($user ? Branch::where('manager_id', $user->user_id)->value('branch_id') : null);
        if ($user && $user->role !== 'Customer' && (!$userBranchId || $order->branch_id != $userBranchId)) {
            return response()->json(['message' => 'Access denied: You can only view orders for your assigned branch.'], 403);
        }

        if ($user && $user->role === 'Customer') {
            $isOwner = ($order->user_id == $user->user_id);
            if (!$isOwner && $user->email_verified_at && !empty($order->customer_email) && strcasecmp(trim($order->customer_email), trim($user->email)) === 0) {
                $isOwner = true;
            }
            if (!$isOwner) {
                return response()->json(['message' => 'Access denied: You can only view your own orders.'], 403);
            }
        }

        return response()->json($order);
    }

    /**
     * Two-Factor Order Tracking (v4.1 Section 8.3 & 11)
     * Requires order_number + (phone OR email).
     * Returns sanitized status stepper info without leaking addresses or payment data.
     */
    public function trackOrder(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|string',
            'contact' => 'required|string',
        ]);

        $orderNumber = trim($validated['order_number']);
        $contact = strtolower(trim($validated['contact']));

        // Try regular bakery orders
        $order = Order::with(['branch', 'items.product', 'statusHistory'])
            ->where('order_number', $orderNumber)
            ->first();

        if ($order) {
            $orderPhone = strtolower(trim($order->customer_phone ?? ''));
            $orderEmail = strtolower(trim($order->customer_email ?? ($order->user?->email ?? '')));
            $shippingPhone = strtolower(trim($order->shipping_address['phone'] ?? ($order->address?->phone ?? '')));

            $matched = ($contact !== '' && ($contact === $orderPhone || $contact === $orderEmail || $contact === $shippingPhone))
                || (strlen($contact) >= 7 && (($orderPhone && str_contains($orderPhone, $contact)) || str_contains($contact, $orderPhone)));

            if ($matched) {
                $statusMap = [
                    'pending' => 'Order Placed',
                    'confirmed' => 'Confirmed',
                    'preparing' => 'In Kitchen / Preparing',
                    'out_for_delivery' => 'Out for Delivery',
                    'ready_for_pickup' => 'Ready for Pickup',
                    'completed' => ($order->order_type === 'delivery' ? 'Delivered' : 'Collected'),
                    'cancelled' => 'Cancelled',
                ];

                return response()->json([
                    'order_number' => $order->order_number,
                    'order_type' => $order->order_type ?? 'delivery',
                    'status' => $order->status,
                    'status_label' => $statusMap[$order->status] ?? ucfirst($order->status),
                    'branch_name' => $order->branch?->branch_name ?? 'SweetBakes Bakery',
                    'created_at' => $order->created_at?->toIso8601String(),
                    'confirmed_at' => $order->confirmed_at?->toIso8601String(),
                    'prepared_at' => $order->prepared_at?->toIso8601String(),
                    'out_for_delivery_at' => $order->dispatched_at?->toIso8601String(),
                    'completed_at' => $order->delivered_at?->toIso8601String(),
                    'items_count' => $order->items->count(),
                    'items_summary' => $order->items->map(fn($i) => [
                        'product_name' => $i->product?->product_name ?? 'Bakery Item',
                        'quantity' => $i->quantity,
                    ]),
                ]);
            }
        }

        // Try custom cake requests
        $cake = CustomCakeRequest::with(['branch', 'user'])
            ->where(function($q) use ($orderNumber) {
                $q->where('request_id', $orderNumber)
                  ->orWhere('request_number', $orderNumber);
            })
            ->first();

        if ($cake) {
            $cakePhone = strtolower(trim($cake->customer_phone ?? ''));
            $cakeEmail = strtolower(trim($cake->user?->email ?? ''));

            $matched = ($contact !== '' && ($contact === $cakePhone || $contact === $cakeEmail))
                || (strlen($contact) >= 7 && (($cakePhone && str_contains($cakePhone, $contact)) || str_contains($contact, $cakePhone)));

            if ($matched) {
                $cakeStatusMap = [
                    'Pending' => 'Order Placed',
                    'Approved' => 'Confirmed',
                    'In Kitchen' => 'In Kitchen / Preparing',
                    'Completed' => 'Delivered / Collected',
                    'Rejected' => 'Cancelled',
                ];

                return response()->json([
                    'order_number' => $cake->request_number ?: ('CCR-' . $cake->request_id),
                    'order_type' => 'custom_cake',
                    'status' => $cake->status,
                    'status_label' => $cakeStatusMap[$cake->status] ?? $cake->status,
                    'branch_name' => $cake->branch?->branch_name ?? 'SweetBakes Bakery',
                    'created_at' => $cake->created_at?->toIso8601String(),
                    'delivery_date' => $cake->needed_by ? $cake->needed_by->format('Y-m-d') : null,
                    'items_count' => 1,
                    'items_summary' => [
                        [
                            'product_name' => 'Custom Celebration Cake (' . ($cake->flavor ?? 'Bespoke') . ')',
                            'quantity' => 1,
                        ]
                    ],
                ]);
            }
        }

        // Anti-enumeration: generic 404 message
        return response()->json([
            'message' => 'Order not found. Please verify your order number and the phone number or email used at checkout.'
        ], 404);
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
    public function store(StoreOrderRequest $request)
    {
        $user = $request->user();
        if (!$user && empty($request->input('customer_phone')) && empty($request->input('customer_name'))) {
            return response()->json([
                'message' => 'Please provide your contact name and phone number or sign in to complete your order.'
            ], 422);
        }

        $branchId = $request->input('branch_id');
        $branch = \App\Models\Branch::find($branchId);
        if ($branch && (!$branch->is_shopping_enabled || $branch->status !== 'Active')) {
            return response()->json([
                'message' => 'Sorry, this branch is currently unavailable for shopping.'
            ], 422);
        }

        $idempotencyKey = $request->header('Idempotency-Key') ?? $request->input('idempotency_key');

        try {
            $result = $this->orderService->createOrder($request->validated(), $user, $idempotencyKey);
            $status = $result['is_replay'] ? 200 : 201;
            $headers = $result['is_replay'] ? ['X-Idempotent-Replay' => 'true'] : [];

            return response()->json($result['order'], $status, $headers);
        } catch (\Exception $e) {
            $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 422;
            return response()->json(['message' => $e->getMessage()], $code);
        }
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

        // Operational order management belongs exclusively to Branch Sellers and Branch Managers
        if ($user && $user->role === 'Super Admin') {
            return response()->json([
                'message' => 'Super Admin does not manage branch operational orders. Order management belongs exclusively to Branch Sellers and Branch Managers.',
            ], 403);
        }

        // Branch scoping: Branch Managers and staff can only update their assigned branch
        $userBranchId = $user?->branch_id ?? ($user ? Branch::where('manager_id', $user->user_id)->value('branch_id') : null);
        if (!$userBranchId || $order->branch_id != $userBranchId) {
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
                'user_name' => $user ? ($user->full_name . ' (' . $user->role . ')') : 'Branch Staff',
                'action' => 'Order status updated',
                'record' => $order->order_number . ($order->branch ? " · {$order->branch->branch_name}" : ""),
                'entity_type' => 'Order',
                'entity_id' => (string) $order->order_id,
                'old_value' => $oldStatus,
                'new_value' => $newStatus,
                'created_at' => now(),
            ]);

            // Branch Seller / Staff Notifications (Strictly important status changes, sent only once per status change)
            $importantStaffStatuses = ['Preparing', 'Out for delivery', 'Ready'];
            if (in_array($newStatus, $importantStaffStatuses) && $oldStatus !== $newStatus) {
                $alreadySentStaff = Notification::where('branch_id', $order->branch_id)
                    ->where('category', 'Orders')
                    ->where('title', 'like', "%{$order->order_number}%")
                    ->where('title', 'like', "%{$newStatus}%")
                    ->exists();

                if (!$alreadySentStaff) {
                    $staffTitle = match ($newStatus) {
                        'Preparing' => "Preparation Started: {$order->order_number}",
                        'Out for delivery' => "Out for Delivery: {$order->order_number}",
                        'Ready' => "Ready for Pickup: {$order->order_number}",
                        default => "Order {$order->order_number} is {$newStatus}",
                    };

                    $staffMessage = match ($newStatus) {
                        'Preparing' => "Kitchen has started preparing {$order->order_number} ({$order->customer_name}).",
                        'Out for delivery' => "{$order->order_number} is dispatched and on the way to {$order->customer_name}.",
                        'Ready' => "{$order->order_number} is packed and ready for customer pickup.",
                        default => "Status updated to {$newStatus}.",
                    };

                    Notification::create([
                        'user_id' => null,
                        'branch_id' => $order->branch_id,
                        'category' => 'Orders',
                        'title' => $staffTitle,
                        'message' => $staffMessage,
                        'link' => '/admin/orders',
                    ]);
                }
            }

            // Customer notification (Only sent once per status change)
            if ($order->user_id && $oldStatus !== $newStatus) {
                $alreadySentCust = Notification::where('user_id', $order->user_id)
                    ->where('title', 'like', "%{$order->order_number}%")
                    ->where('title', 'like', "%{$newStatus}%")
                    ->exists();

                if (!$alreadySentCust) {
                    Notification::create([
                        'user_id' => $order->user_id,
                        'branch_id' => $order->branch_id,
                        'category' => 'Orders',
                        'title' => "Order {$order->order_number}: {$newStatus}",
                        'message' => "Your order status is now {$newStatus}.",
                        'link' => "/orders/{$order->order_number}",
                    ]);
                }
            }
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
                'record' => $order->order_number . ($order->branch ? " · {$order->branch->branch_name}" : ""),
                'entity_type' => 'Order',
                'entity_id' => (string) $order->order_id,
                'old_value' => $oldStatus,
                'new_value' => 'Cancelled',
                'created_at' => now(),
            ]);

            $alreadyCancelledNotif = Notification::where('branch_id', $order->branch_id)
                ->where('category', 'Orders')
                ->where('title', 'like', "%{$order->order_number}%")
                ->where('title', 'like', '%cancelled%')
                ->exists();

            if (!$alreadyCancelledNotif) {
                Notification::create([
                    'user_id' => null,
                    'branch_id' => $order->branch_id,
                    'category' => 'Orders',
                    'title' => "Order {$order->order_number} Cancelled",
                    'message' => "Order {$order->order_number} for {$order->branch?->branch_name} was cancelled.",
                    'link' => '/admin/orders',
                ]);
            }
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

    /**
     * Delete/archive a finished order from history (Customer or Admin)
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            $tokenString = $request->bearerToken();
            if ($tokenString) {
                $token = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenString);
                $user = $token?->tokenable;
            }
        }

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $order = Order::where('order_id', $id)
            ->orWhere('order_number', $id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        // Customer scoping: Customers can only delete their own orders
        if ($user->role === 'Customer') {
            $isOwner = ($order->user_id && $order->user_id == $user->user_id) ||
                       (!empty($user->phone) && $order->customer_phone === $user->phone);
            if (!$isOwner) {
                return response()->json(['message' => 'Access denied: You can only remove your own orders from history.'], 403);
            }
        } elseif (in_array($user->role, ['Branch Admin', 'Branch Staff']) && $user->branch_id && $order->branch_id != $user->branch_id) {
            return response()->json(['message' => 'Access denied: You can only manage orders for your assigned branch.'], 403);
        }

        // Rule: Only finished orders (Completed, Delivered, Cancelled) can be removed from history
        $finishedStatuses = ['Completed', 'Delivered', 'Cancelled'];
        if (!in_array($order->order_status, $finishedStatuses) && $user->role !== 'Super Admin') {
            return response()->json([
                'message' => "Active order #{$order->order_number} is currently '{$order->order_status}' and cannot be deleted until it is Completed or Cancelled."
            ], 422);
        }

        $orderNumber = $order->order_number;
        $order->delete(); // Soft deletes the order record

        AuditLog::create([
            'user_id' => $user->user_id,
            'user_name' => $user->full_name . ' (' . $user->role . ')',
            'action' => 'Order history deleted',
            'record' => $orderNumber,
            'entity_type' => 'Order',
            'entity_id' => (string) $order->order_id,
            'old_value' => $order->order_status,
            'new_value' => 'Deleted from customer view (Archived)',
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => "Order #{$orderNumber} successfully removed from your order history.",
            'order_id' => $order->order_id,
            'order_number' => $orderNumber,
        ], 200);
    }

    /**
     * Clear all finished (Completed/Cancelled) orders for the customer
     */
    public function clearFinishedHistory(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            $tokenString = $request->bearerToken();
            if ($tokenString) {
                $token = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenString);
                $user = $token?->tokenable;
            }
        }

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $query = Order::whereIn('order_status', ['Completed', 'Delivered', 'Cancelled']);

        if ($user->role === 'Customer') {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->user_id);
                if (!empty($user->phone)) {
                    $q->orWhere('customer_phone', $user->phone);
                }
            });
        }

        $count = $query->count();
        $query->delete(); // Soft-deletes matching finished orders

        return response()->json([
            'message' => "Cleared {$count} finished orders from your order history.",
            'cleared_count' => $count,
        ]);
    }
}
