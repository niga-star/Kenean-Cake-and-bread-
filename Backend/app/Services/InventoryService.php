<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\InventoryTransaction;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\AppSetting;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    /**
     * Release expired stock reservations for unpaid orders
     */
    public function releaseExpiredReservations(): int
    {
        $timeoutMinutes = (int) AppSetting::get('unpaid_order_timeout', 15);
        $cutoff = Carbon::now()->subMinutes($timeoutMinutes);

        $expiredOrders = Order::with('items')
            ->where('order_status', 'Pending')
            ->where('placed_at', '<', $cutoff)
            ->whereHas('payment', function ($pq) {
                $pq->whereIn('payment_method', ['Chapa', 'Telebirr'])
                   ->where('payment_status', 'Pending');
            })
            ->get();

        $releasedCount = 0;

        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order, &$releasedCount, $timeoutMinutes) {
                foreach ($order->items as $item) {
                    $inv = BranchInventory::where('branch_id', $order->branch_id)
                        ->where('product_id', $item->product_id)
                        ->lockForUpdate()
                        ->first();

                    if ($inv) {
                        $inv->reserved_quantity = max(0, $inv->reserved_quantity - $item->quantity);
                        $inv->save();

                        InventoryTransaction::create([
                            'branch_id' => $order->branch_id,
                            'product_id' => $item->product_id,
                            'transaction_type' => 'manual_adjustment',
                            'quantity' => 0,
                            'reference_type' => 'Order',
                            'reference_id' => $order->order_id,
                            'reason' => "Released {$item->quantity} reserved units due to payment timeout ({$timeoutMinutes}m)",
                            'created_by' => null,
                            'created_at' => now(),
                        ]);
                    }
                }

                $order->order_status = 'Cancelled';
                $order->save();

                \App\Models\OrderStatusHistory::create([
                    'order_id' => $order->order_id,
                    'old_status' => 'Pending',
                    'new_status' => 'Cancelled',
                    'changed_by' => null,
                    'note' => "System auto-cancelled: Unpaid online order exceeded {$timeoutMinutes} minutes reservation limit.",
                    'changed_at' => now(),
                ]);

                $releasedCount++;
            }, 3);
        }

        return $releasedCount;
    }

    /**
     * Adjust branch stock with verification, locking, and audit trail
     */
    public function adjustStock(int $branchId, int $productId, string $type, int $quantity, string $reason, ?object $user = null): array
    {
        $branch = Branch::findOrFail($branchId);

        if ($type === 'production') {
            if ($branch->branch_type !== 'Main') {
                throw new Exception('Production received can only be recorded at the Main Branch.', 422);
            }
            if ($quantity <= 0) {
                throw new Exception('Production received quantity must be greater than zero.', 422);
            }
        }

        $qtyDelta = $quantity;
        if (in_array($type, ['damaged', 'expired'])) {
            $qtyDelta = -abs($quantity);
        }

        return DB::transaction(function () use ($branchId, $productId, $type, $quantity, $reason, $user, $qtyDelta, $branch) {
            $inventory = BranchInventory::where('branch_id', $branchId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                $inventory = BranchInventory::create([
                    'branch_id' => $branchId,
                    'product_id' => $productId,
                    'quantity_on_hand' => 0,
                    'reserved_quantity' => 0,
                    'reorder_level' => 10,
                ]);
            }

            $oldOnHand = $inventory->quantity_on_hand;
            $newOnHand = $oldOnHand + $qtyDelta;

            if ($newOnHand < 0) {
                throw new Exception("Adjustment would result in negative stock on hand ({$newOnHand}).", 422);
            }

            if ($newOnHand < $inventory->reserved_quantity) {
                throw new Exception("Stock on hand cannot be reduced below current reserved quantity ({$inventory->reserved_quantity}).", 422);
            }

            $inventory->quantity_on_hand = $newOnHand;
            $inventory->save();

            $dbType = in_array($type, ['production', 'transfer_dispatch', 'transfer_receive', 'order_placed', 'order_completed', 'order_cancelled', 'damaged', 'expired', 'manual_adjustment'])
                ? $type
                : 'manual_adjustment';

            $tx = InventoryTransaction::create([
                'branch_id' => $branchId,
                'product_id' => $productId,
                'transaction_type' => $dbType,
                'quantity' => $qtyDelta,
                'reference_type' => 'manual',
                'reference_id' => $inventory->inventory_id ?? null,
                'reason' => $reason,
                'created_by' => $user?->user_id,
                'created_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => $user?->user_id,
                'user_name' => $user ? ($user->full_name . ' (' . $user->role . ')') : 'System Admin',
                'action' => 'Stock adjustment',
                'record' => ($inventory->product?->product_name ?? 'Product #' . $productId) . " @ {$branch->branch_name}",
                'entity_type' => 'BranchInventory',
                'entity_id' => (string) $inventory->inventory_id,
                'old_value' => "On hand: {$oldOnHand}",
                'new_value' => "On hand: {$newOnHand} (Delta: " . ($qtyDelta >= 0 ? "+{$qtyDelta}" : $qtyDelta) . ", Reason: {$reason})",
                'created_at' => now(),
            ]);

            if ($inventory->quantity_on_hand <= $inventory->reorder_level) {
                $prodName = $inventory->product?->product_name ?? 'Product #' . $productId;
                Notification::create([
                    'user_id' => null,
                    'category' => 'Stock',
                    'title' => 'Low Stock Warning',
                    'message' => "{$prodName} at {$branch->branch_name} is at {$inventory->quantity_on_hand} units (reorder level: {$inventory->reorder_level}).",
                    'link' => '/admin/inventory',
                ]);
            }

            return [
                'inventory' => $inventory->fresh(['product', 'branch']),
                'transaction' => $tx,
                'old_quantity' => $oldOnHand,
                'new_quantity' => $newOnHand,
                'delta' => $qtyDelta,
            ];
        }, 3);
    }
}
