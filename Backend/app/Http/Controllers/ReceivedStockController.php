<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ReceivedStock;
use App\Models\StockDispatch;
use App\Models\Branch;
use App\Models\Product;
use App\Models\BranchInventory;
use App\Models\InventoryTransaction;
use App\Models\AuditLog;
use App\Models\Notification;

class ReceivedStockController extends Controller
{
    /**
     * List received stock records and latest received stock info for a branch.
     * Scoped strictly: each branch only sees its own received stock.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $isSuperAdmin = ($user->role === 'Super Admin');
        $branchId = null;

        if ($isSuperAdmin) {
            if ($request->filled('branch_id') && $request->branch_id !== 'all') {
                $branchId = (int) $request->branch_id;
            }
        } else {
            $branchId = $user->branch_id ?? Branch::where('manager_id', $user->user_id)->value('branch_id');
        }

        // Check if target branch is Main Branch (strictly by branch_type = 'Main', v4.1 Section 5)
        $branch = $branchId ? Branch::find($branchId) : null;
        $isMainBranch = $branch && ($branch->branch_type === 'Main');

        $query = ReceivedStock::with(['product.category', 'receiver', 'dispatch'])
            ->orderBy('date_received', 'desc')
            ->orderBy('received_stock_id', 'desc');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        } elseif (!$isSuperAdmin) {
            return response()->json([
                'received_stocks' => [],
                'latest_received' => null,
                'is_main_branch' => false,
                'in_transit_dispatches' => [],
            ]);
        }

        $records = $query->take(100)->get()->map(function ($r) {
            return [
                'received_stock_id' => $r->received_stock_id,
                'branch_id' => $r->branch_id,
                'dispatch_id' => $r->dispatch_id,
                'dispatch_reference' => $r->dispatch?->reference_number,
                'product_id' => $r->product_id,
                'product_name' => $r->product_name,
                'category_name' => $r->category_name,
                'quantity_received' => $r->quantity_received,
                'date_received' => $r->date_received ? $r->date_received->format('Y-m-d') : null,
                'formatted_date' => $r->date_received ? $r->date_received->format('M d, Y') : 'Recent',
                'receiver_name' => $r->receiver_name ?: ($r->receiver?->full_name ?? 'Branch Staff'),
                'reference_number' => $r->reference_number,
                'notes' => $r->notes,
                'created_at' => $r->created_at ? $r->created_at->toIso8601String() : null,
            ];
        });

        $latest = $records->first() ?: null;

        // Fetch dispatches awaiting receipt for this branch (v4.1 Section 5)
        $inTransitDispatches = [];
        if ($branchId && !$isMainBranch) {
            $inTransitDispatches = StockDispatch::with(['items.product.category', 'fromBranch'])
                ->where('to_branch_id', $branchId)
                ->whereIn('status', ['in_transit', 'partially_received'])
                ->orderBy('sent_at', 'desc')
                ->get()
                ->map(fn($d) => [
                    'dispatch_id' => $d->dispatch_id,
                    'reference_number' => $d->reference_number,
                    'from_branch_name' => $d->fromBranch?->branch_name,
                    'status' => $d->status,
                    'sent_at' => $d->sent_at ? $d->sent_at->toIso8601String() : null,
                    'items' => $d->items->map(fn($i) => [
                        'id' => $i->id,
                        'product_id' => $i->product_id,
                        'product_name' => $i->product?->product_name ?? 'Product',
                        'category_name' => $i->product?->category?->category_name ?? 'Bakery',
                        'quantity_dispatched' => (int) $i->quantity_dispatched,
                        'quantity_received' => (int) $i->quantity_received,
                        'remaining_to_receive' => max(0, (int) $i->quantity_dispatched - (int) $i->quantity_received),
                    ]),
                ]);
        }

        return response()->json([
            'received_stocks' => $records,
            'latest_received' => $latest,
            'in_transit_dispatches' => $inTransitDispatches,
            'branch_id' => $branchId,
            'branch_name' => $branch?->branch_name,
            'is_main_branch' => $isMainBranch,
            'total_received_units' => (int) $records->sum('quantity_received'),
        ]);
    }

    /**
     * Store a new stock receive record for a branch against a dispatch manifest.
     * POST /api/admin/received-stocks
     * Rules (v4.1 Section 3 & 5):
     * - Every receipt must reference a dispatch (dispatch_id).
     * - Branch Managers and Branch Staff receive; Super Admin views only (403 for Super Admin).
     * - Destination branch cannot be Main Branch (422 strictly by branch_type = 'Main').
     * - Receiving against a dispatch addressed to another branch returns 403.
     * - Quantity received cannot exceed dispatched quantity (422).
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Rule (v4.1 Section 3): Super Admin can no longer receive stock
        if ($user->role === 'Super Admin') {
            return response()->json([
                'message' => 'Access denied: Super Admin cannot receive stock. Stock receipts must be performed by the destination Branch Manager or Branch Staff.'
            ], 403);
        }

        // Roles permitted to receive stock: Branch Manager, Branch Staff, Branch Admin
        $allowedReceiveRoles = ['Branch Manager', 'Branch Admin', 'Branch Staff', 'Main Branch Manager', 'Servant'];
        if (!in_array($user->role, $allowedReceiveRoles)) {
            return response()->json([
                'message' => 'Access denied: Only Branch Managers and Branch Staff are authorized to receive stock.'
            ], 403);
        }

        $userBranchId = $user->branch_id ?? Branch::where('manager_id', $user->user_id)->value('branch_id');
        if (!$userBranchId) {
            return response()->json(['message' => 'Your account is not assigned to a valid branch.'], 403);
        }

        $branch = Branch::findOrFail($userBranchId);

        // Rule (v4.1 Section 5): Main Branch never receives stock
        if ($branch->branch_type === 'Main') {
            return response()->json([
                'message' => 'Stock Receive is only available for regular branch locations. Main Branch distributes stock and never receives transfers.'
            ], 422);
        }

        $validated = $request->validate([
            'dispatch_id' => 'required|exists:stock_dispatches,dispatch_id',
            'product_id' => 'required|exists:products,product_id',
            'quantity_received' => 'required|integer|min:1|max:100000',
            'date_received' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
            'reference_number' => 'nullable|string|max:100',
        ]);

        $dispatch = StockDispatch::with(['items', 'fromBranch', 'toBranch'])->findOrFail($validated['dispatch_id']);

        // Rule (v4.1 Section 5): Receipt against dispatch addressed to another branch returns 403
        if ((int) $dispatch->to_branch_id !== (int) $branch->branch_id) {
            return response()->json([
                'message' => "Access denied: Dispatch manifest {$dispatch->reference_number} is addressed to {$dispatch->toBranch->branch_name}, not your branch."
            ], 403);
        }

        // Must be in_transit or partially_received
        if (!in_array($dispatch->status, ['in_transit', 'partially_received'])) {
            return response()->json([
                'message' => "Dispatch manifest {$dispatch->reference_number} cannot be received because its status is '{$dispatch->status}'."
            ], 422);
        }

        // Check line item in dispatch
        $dispatchItem = $dispatch->items->firstWhere('product_id', $validated['product_id']);
        if (!$dispatchItem) {
            return response()->json([
                'message' => "The selected product is not part of dispatch manifest {$dispatch->reference_number}."
            ], 422);
        }

        $remainingDispatched = (int) $dispatchItem->quantity_dispatched - (int) $dispatchItem->quantity_received;
        if ((int) $validated['quantity_received'] > $remainingDispatched) {
            return response()->json([
                'message' => "Received quantity ({$validated['quantity_received']}) exceeds the remaining dispatched quantity ({$remainingDispatched}) on manifest {$dispatch->reference_number}."
            ], 422);
        }

        $product = Product::with('category')->findOrFail($validated['product_id']);
        $categoryName = $product->category?->category_name ?? 'Bakery';

        $dateReceived = !empty($validated['date_received']) 
            ? Carbon::parse($validated['date_received'])->toDateString() 
            : now()->toDateString();

        $refNumber = !empty($validated['reference_number'])
            ? $validated['reference_number']
            : ('RCV-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4)));

        $receivedRecord = DB::transaction(function () use ($branch, $dispatch, $dispatchItem, $product, $categoryName, $validated, $dateReceived, $refNumber, $user) {
            // 1. Create ReceivedStock record linked to dispatch_id
            $record = ReceivedStock::create([
                'branch_id' => $branch->branch_id,
                'dispatch_id' => $dispatch->dispatch_id,
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'category_name' => $categoryName,
                'quantity_received' => (int) $validated['quantity_received'],
                'date_received' => $dateReceived,
                'received_by' => $user->user_id,
                'receiver_name' => $user->full_name,
                'reference_number' => $refNumber,
                'notes' => $validated['notes'] ?? null,
            ]);

            // 2. Update destination branch inventory
            $inventory = BranchInventory::firstOrCreate(
                [
                    'branch_id' => $branch->branch_id,
                    'product_id' => $product->product_id,
                ],
                [
                    'quantity_on_hand' => 0,
                    'reserved_quantity' => 0,
                    'reorder_level' => 5,
                ]
            );

            $oldOnHand = $inventory->quantity_on_hand;
            $inventory->quantity_on_hand += (int) $validated['quantity_received'];
            $inventory->save();

            // 3. Update dispatch item received quantity
            $dispatchItem->quantity_received += (int) $validated['quantity_received'];
            $dispatchItem->save();

            // 4. Update dispatch status
            $allDone = true;
            foreach ($dispatch->items()->get() as $item) {
                if ($item->quantity_received < $item->quantity_dispatched) {
                    $allDone = false;
                }
            }

            if ($allDone) {
                $dispatch->status = 'received';
            } else {
                $dispatch->status = 'partially_received';
            }
            $dispatch->save();

            // 5. Record immutable InventoryTransaction
            InventoryTransaction::create([
                'branch_id' => $branch->branch_id,
                'product_id' => $product->product_id,
                'transaction_type' => 'transfer_receive',
                'quantity' => (int) $validated['quantity_received'],
                'reference_type' => 'StockDispatch',
                'reference_id' => $dispatch->dispatch_id,
                'reason' => "Stock Receive from {$dispatch->fromBranch->branch_name} (Manifest: {$dispatch->reference_number})",
                'created_by' => $user->user_id,
            ]);

            // 6. Record AuditLog
            AuditLog::create([
                'user_id' => $user->user_id,
                'user_name' => $user->full_name . ' (' . $user->role . ')',
                'action' => 'Stock received against dispatch',
                'record' => "{$product->product_name} (+{$validated['quantity_received']}) · Manifest {$dispatch->reference_number} at {$branch->branch_name}",
                'entity_type' => 'StockDispatch',
                'entity_id' => (string) $dispatch->dispatch_id,
                'old_value' => "On hand: {$oldOnHand}",
                'new_value' => "On hand: {$inventory->quantity_on_hand} (+{$validated['quantity_received']} units)",
                'created_at' => now(),
            ]);

            // 7. Send notification to branch staff
            Notification::create([
                'user_id' => null,
                'branch_id' => $branch->branch_id,
                'category' => 'Stock',
                'title' => "Stock Received: {$product->product_name}",
                'message' => "Received +{$validated['quantity_received']} units of {$product->product_name} against manifest {$dispatch->reference_number}.",
                'link' => '/admin/inventory',
            ]);

            // 8. Notify Main Branch Manager
            Notification::create([
                'user_id' => null,
                'branch_id' => $dispatch->from_branch_id,
                'category' => 'Stock',
                'title' => "Shipment Received at {$branch->branch_name}",
                'message' => "{$branch->branch_name} received {$validated['quantity_received']} units of {$product->product_name} on manifest {$dispatch->reference_number}. Status: {$dispatch->status}.",
                'link' => '/admin/distribution',
            ]);

            return $record;
        });

        $formatted = [
            'received_stock_id' => $receivedRecord->received_stock_id,
            'branch_id' => $receivedRecord->branch_id,
            'dispatch_id' => $receivedRecord->dispatch_id,
            'product_id' => $receivedRecord->product_id,
            'product_name' => $receivedRecord->product_name,
            'category_name' => $receivedRecord->category_name,
            'quantity_received' => $receivedRecord->quantity_received,
            'date_received' => $receivedRecord->date_received->format('Y-m-d'),
            'formatted_date' => $receivedRecord->date_received->format('M d, Y'),
            'receiver_name' => $receivedRecord->receiver_name,
            'reference_number' => $receivedRecord->reference_number,
            'notes' => $receivedRecord->notes,
        ];

        return response()->json([
            'message' => "Successfully received {$validated['quantity_received']} units of {$product->product_name} at {$branch->branch_name} on manifest {$dispatch->reference_number}!",
            'received_stock' => $formatted,
            'latest_received' => $formatted,
            'dispatch_status' => $dispatch->status,
        ], 201);
    }
}
