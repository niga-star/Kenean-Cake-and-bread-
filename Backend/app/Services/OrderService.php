<?php

namespace App\Services;

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
use App\Models\DeliveryZone;
use App\Models\Promotion;
use App\Models\Address;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Exception;

class OrderService
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create an order with idempotency check, stock reservation, and transaction boundary
     */
    public function createOrder(array $validated, ?object $user = null, ?string $idempotencyKey = null): array
    {
        // 1. Release expired unpaid orders before processing
        $this->inventoryService->releaseExpiredReservations();

        // 2. Check idempotency replay
        if ($idempotencyKey) {
            $cachedOrderId = Cache::get("order_idempotency:{$idempotencyKey}");
            if ($cachedOrderId) {
                $existingOrder = Order::with(['branch', 'user', 'items.product', 'payment', 'statusHistory'])->find($cachedOrderId);
                if ($existingOrder) {
                    return [
                        'order' => $existingOrder,
                        'is_replay' => true,
                    ];
                }
            }
        }

        $branch = Branch::findOrFail($validated['branch_id']);
        if ($branch->status !== 'Active' || !$branch->is_shopping_enabled) {
            throw new Exception('Sorry, this branch is currently unavailable for shopping.', 422);
        }

        // 3. Resolve customer identity
        $customerName = trim($validated['customer_name'] ?? '');
        $customerPhone = trim($validated['customer_phone'] ?? '');

        $orderUserId = null;
        if ($user && $user->role === 'Customer') {
            $orderUserId = $user->user_id;
            $customerName = $customerName ?: $user->full_name;
            $customerPhone = $customerPhone ?: $user->phone;
        } elseif (!empty($customerPhone)) {
            $existingCustomer = User::where('phone', $customerPhone)->first();
            if ($existingCustomer) {
                $orderUserId = $existingCustomer->user_id;
                $customerName = $customerName ?: $existingCustomer->full_name;
            } else {
                $realCustomer = User::create([
                    'full_name' => $customerName ?: 'Customer',
                    'email' => 'customer_' . time() . '_' . mt_rand(100, 999) . '@sweetbakes.local',
                    'phone' => $customerPhone,
                    'password' => Hash::make(bin2hex(random_bytes(16))),
                    'role' => 'Customer',
                    'status' => 'Active',
                ]);
                $orderUserId = $realCustomer->user_id;
            }
        } elseif ($user) {
            $orderUserId = $user->user_id;
            $customerName = $customerName ?: $user->full_name;
            $customerPhone = $customerPhone ?: $user->phone;
        } else {
            $guestCustomer = User::create([
                'full_name' => $customerName ?: 'Guest Customer',
                'email' => 'guest_' . time() . '_' . mt_rand(100, 999) . '@sweetbakes.local',
                'phone' => null,
                'password' => Hash::make(bin2hex(random_bytes(16))),
                'role' => 'Customer',
                'status' => 'Active',
            ]);
            $orderUserId = $guestCustomer->user_id;
        }

        $customerName = $customerName ?: 'Customer';

        // 4. Resolve delivery address
        $addressId = $validated['address_id'] ?? null;
        if ($validated['fulfillment_method'] === 'Delivery' && empty($addressId)) {
            $address = Address::create([
                'user_id' => $orderUserId,
                'city' => $validated['city'] ?? $branch->city ?? 'Dessie',
                'region' => $validated['region'] ?? $branch->region ?? 'Amhara',
                'street' => $validated['delivery_address'] ?? 'Customer Delivery Address',
                'notes' => $validated['order_notes'] ?? 'Deliver to doorstep',
                'is_default' => true,
            ]);
            $addressId = $address->address_id;
        }

        // 5. Calculate delivery fee
        $deliveryFee = 0.00;
        if ($validated['fulfillment_method'] === 'Delivery') {
            $zoneId = $validated['zone_id'] ?? $validated['delivery_zone_id'] ?? null;
            if ($zoneId) {
                $zone = DeliveryZone::where('branch_id', $branch->branch_id)
                    ->where('zone_id', $zoneId)
                    ->where('status', 'Active')
                    ->first();
                if (!$zone) {
                    throw new Exception('The selected delivery zone is not an active delivery zone for this branch.', 422);
                }
                $deliveryFee = (float) $zone->delivery_fee;
            } else {
                $activeZone = DeliveryZone::where('branch_id', $branch->branch_id)->where('status', 'Active')->first();
                if (!$activeZone) {
                    throw new Exception("{$branch->branch_name} does not have active delivery zones. Please select Shop Pickup or another branch.", 422);
                }
                $deliveryFee = (float) $activeZone->delivery_fee;
            }
        }

        // 6. Execute atomic stock reservation and order creation inside database transaction with retries
        $order = DB::transaction(function () use ($validated, $branch, $deliveryFee, $orderUserId, $customerName, $customerPhone, $user, $addressId) {
            $rand = mt_rand(1000, 9999);
            $orderNumber = "ORD-{$rand}";
            while (Order::where('order_number', $orderNumber)->exists()) {
                $orderNumber = "ORD-" . mt_rand(1000, 9999);
            }

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
                    throw new Exception("Insufficient stock for {$product->product_name} at {$branch->branch_name}. Requested: {$qty}, Available: {$available}.", 422);
                }

                $inv->reserved_quantity += $qty;
                $inv->save();

                if ($inv->available_quantity <= $inv->reorder_level) {
                    Notification::create([
                        'user_id' => null,
                        'category' => 'Stock',
                        'title' => 'Low stock',
                        'message' => "{$branch->branch_name} has {$inv->available_quantity} {$product->product_name} left (at or below reorder level {$inv->reorder_level}).",
                        'link' => '/admin/inventory',
                    ]);
                }

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
                    'created_at' => now(),
                ]);
            }

            // Promo calculation
            $discountAmount = 0.00;
            $rawPromoCode = strtoupper(trim($validated['promo_code'] ?? $validated['promotion_code'] ?? ''));
            if (!empty($rawPromoCode)) {
                $promo = Promotion::whereRaw('UPPER(promotion_name) = ?', [$rawPromoCode])
                    ->where('status', 'Active')
                    ->first();
                if ($promo) {
                    $today = Carbon::today();
                    $isDateValid = (!$promo->start_date || Carbon::parse($promo->start_date)->startOfDay()->lte($today)) &&
                                   (!$promo->end_date || Carbon::parse($promo->end_date)->endOfDay()->gte($today));
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
            $isOnline = in_array($validated['payment_method'], ['Chapa', 'Telebirr', 'CBE Birr']);
            $initialStatus = $isOnline ? 'Pending' : 'Confirmed';

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $orderUserId,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'branch_id' => $branch->branch_id,
                'address_id' => $addressId,
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

            OrderStatusHistory::create([
                'order_id' => $order->order_id,
                'old_status' => null,
                'new_status' => $initialStatus,
                'changed_by' => $user?->user_id,
                'note' => $isOnline ? 'Order placed, awaiting payment confirmation' : 'Order placed, accepted for pay-on-pickup/delivery',
                'changed_at' => now(),
            ]);

            $txPrefix = $validated['payment_method'] === 'Chapa' 
                ? 'CH-' 
                : ($validated['payment_method'] === 'Telebirr' 
                    ? 'TB-' 
                    : ($validated['payment_method'] === 'CBE Birr' ? 'CBE-' : 'PAY-'));
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
                'user_name' => $customerName,
                'action' => 'Order placed',
                'record' => $order->order_number . ($branch ? " · {$branch->branch_name}" : ""),
                'entity_type' => 'Order',
                'entity_id' => (string) $order->order_id,
                'old_value' => '—',
                'new_value' => "Total ETB {$totalAmount} ({$validated['payment_method']})",
                'created_at' => now(),
            ]);

            $alreadyNotifiedOrder = Notification::where('branch_id', $branch->branch_id)
                ->where('category', 'Orders')
                ->where('title', 'like', "%{$order->order_number}%")
                ->exists();

            if (!$alreadyNotifiedOrder) {
                Notification::create([
                    'user_id' => null,
                    'branch_id' => $branch->branch_id,
                    'category' => 'Orders',
                    'title' => "New Order: {$order->order_number}",
                    'message' => "Order from {$customerName} (ETB " . number_format($totalAmount, 0) . ")",
                    'link' => '/admin/orders',
                ]);
            }

            if ($orderUserId) {
                Notification::create([
                    'user_id' => $orderUserId,
                    'branch_id' => $branch->branch_id,
                    'category' => 'Orders',
                    'title' => 'Order Placed Successfully',
                    'message' => "Your order {$order->order_number} has been received by {$branch->branch_name} and is being prepared.",
                    'link' => "/orders/{$order->order_number}",
                ]);
            }

            return $order;
        }, 3);

        $loadedOrder = $order->fresh(['branch', 'user', 'items.product', 'payment', 'statusHistory']);

        if ($idempotencyKey) {
            Cache::put("order_idempotency:{$idempotencyKey}", $order->order_id, 86400);
        }

        return [
            'order' => $loadedOrder,
            'is_replay' => false,
        ];
    }
}
