<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StockDispatch;
use App\Models\StockDispatchItem;
use App\Models\ReceivedStock;
use App\Models\Branch;
use App\Models\Product;
use App\Models\BranchInventory;
use App\Models\InventoryTransaction;
use App\Models\AuditLog;
use App\Models\Notification;
use Carbon\Carbon;

class StockDispatchController extends Controller
{
    /**
     * List dispatches with branch scoping.
     * GET /api/admin/dispatches
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $isSuperAdmin = ($user->role === 'Super Admin');
        $userBranchId = $user->branch_id ?? Branch::where('manager_id', $user->user_id)->value('branch_id');

        $query = StockDispatch::with(['fromBranch', 'toBranch', 'creator', 'items.product.category'])
            ->orderBy('created_at', 'desc');

        if ($isSuperAdmin) {
            if ($request->filled('branch_id') && $request->branch_id !== 'all') {
                $bId = (int) $request->branch_id;
                $query->where(function ($q) use ($bId) {
                    $q->where('from_branch_id', $bId)->orWhere('to_branch_id', $bId);
                });
            }
        } else {
            if (!$userBranchId) {
                return response()->json(['dispatches' => []]);
            }
            $query->where(function ($q) use ($userBranchId) {
                $q->where('from_branch_id', $userBranchId)->orWhere('to_branch_id', $userBranchId);
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $dispatches = $query->take(100)->get()->map(function ($d) {
            return [
                'dispatch_id' => $d->dispatch_id,
                'reference_number' => $d->reference_number,
                'from_branch_id' => $d->from_branch_id,
                'from_branch_name' => $d->fromBranch?->branch_name,
                'to_branch_id' => $d->to_branch_id,
                'to_branch_name' => $d->toBranch?->branch_name,
                'status' => $d->status,
                'notes' => $d->notes,
                'creator_name' => $d->creator?->full_name,
                'sent_at' => $d->sent_at ? $d->sent_at->toIso8601String() : null,
                'created_at' => $d->created_at ? $d->created_at->toIso8601String() : null,
                'items_count' => $d->items->count(),
                'total_units_dispatched' => (int) $d->items->sum('quantity_dispatched'),
                'total_units_received' => (int) $d->items->sum('quantity_received'),
                'items' => $d->items->map(fn($i) => [
                    'id' => $i->id,
                    'product_id' => $i->product_id,
                    'product_name' => $i->product?->product_name ?? 'Product',
                    'category_name' => $i->product?->category?->category_name ?? 'Bakery',
                    'quantity_dispatched' => (int) $i->quantity_dispatched,
                    'quantity_received' => (int) $i->quantity_received,
                    'is_complete' => ($i->quantity_received >= $i->quantity_dispatched),
                ]),
            ];
        });

        return response()->json(['dispatches' => $dispatches]);
    }

    /**
     * Show single dispatch.
     * GET /api/admin/dispatches/{id}
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $dispatch = StockDispatch::with(['fromBranch', 'toBranch', 'creator', 'items.product.category'])
            ->findOrFail($id);

        if ($user->role !== 'Super Admin') {
            $userBranchId = $user->branch_id ?? Branch::where('manager_id', $user->user_id)->value('branch_id');
            if ($dispatch->from_branch_id != $userBranchId && $dispatch->to_branch_id != $userBranchId) {
                return response()->json(['message' => 'Access denied: You can only view dispatches involving your assigned branch.'], 403);
            }
        }

        return response()->json($dispatch);
    }

    /**
     * Create a draft dispatch (Main Branch Manager only).
     * POST /api/admin/dispatches
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Only Main Branch Manager can create a dispatch (v4.1 Section 3 & 5)
        $mainBranch = Branch::where('branch_type', 'Main')->first();
        if (!$mainBranch) {
            return response()->json(['message' => 'No Main Branch configured in the system.'], 422);
        }

        $userBranchId = $user->branch_id ?? Branch::where('manager_id', $user->user_id)->value('branch_id');
        $isMainManager = in_array($user->role, ['Main Branch Manager']) || ($user->role === 'Branch Manager' && (int) $userBranchId === (int) $mainBranch->branch_id);

        if (!$isMainManager && $user->role !== 'Super Admin') {
            return response()->json(['message' => 'Access denied: Only the Main Branch Manager can create stock dispatches.'], 403);
        }

        $items = $request->input('items', []);
        if (is_array($items)) {
            $normalizedItems = [];
            foreach ($items as $item) {
                if (is_array($item)) {
                    if (isset($item['quantity']) && !isset($item['quantity_dispatched'])) {
                        $item['quantity_dispatched'] = $item['quantity'];
                    }
                    $normalizedItems[] = $item;
                }
            }
            $request->merge(['items' => $normalizedItems]);
        }

        $validated = $request->validate([
            'to_branch_id' => 'required|exists:branches,branch_id',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.quantity_dispatched' => 'required|integer|min:1|max:100000',
        ]);

        $destBranch = Branch::findOrFail($validated['to_branch_id']);
        if ($destBranch->branch_type === 'Main') {
            return response()->json(['message' => 'Destination branch cannot be the Main Branch.'], 422);
        }

        $refNumber = 'DSP-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));

        $dispatch = DB::transaction(function () use ($mainBranch, $destBranch, $validated, $refNumber, $user) {
            $dsp = StockDispatch::create([
                'from_branch_id' => $mainBranch->branch_id,
                'to_branch_id' => $destBranch->branch_id,
                'status' => 'draft',
                'reference_number' => $refNumber,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $user->user_id,
            ]);

            foreach ($validated['items'] as $item) {
                StockDispatchItem::create([
                    'dispatch_id' => $dsp->dispatch_id,
                    'product_id' => $item['product_id'],
                    'quantity_dispatched' => (int) $item['quantity_dispatched'],
                    'quantity_received' => 0,
                ]);
            }

            return $dsp;
        });

        return response()->json([
            'message' => "Stock dispatch manifest {$refNumber} created successfully in draft state.",
            'dispatch' => $dispatch->load(['items.product', 'toBranch']),
        ], 201);
    }

    /**
     * Confirm and send dispatch shipment.
     * Decrements Main Branch stock, writes transfer_dispatch transaction, sets status to in_transit.
     * POST /api/admin/dispatches/{id}/send
     */
    public function send(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $dispatch = StockDispatch::with(['items.product.category', 'fromBranch', 'toBranch'])->findOrFail($id);

        if ($dispatch->status !== 'draft') {
            return response()->json(['message' => "Dispatch cannot be sent because it is in '{$dispatch->status}' status."], 422);
        }

        // Verify Main Branch Manager authority
        $mainBranch = $dispatch->fromBranch;
        $userBranchId = $user->branch_id ?? Branch::where('manager_id', $user->user_id)->value('branch_id');
        $isMainManager = in_array($user->role, ['Main Branch Manager']) || ($user->role === 'Branch Manager' && (int) $userBranchId === (int) $mainBranch->branch_id);

        if (!$isMainManager && $user->role !== 'Super Admin') {
            return response()->json(['message' => 'Access denied: Only the Main Branch Manager can dispatch stock shipments.'], 403);
        }

        // Verify stock availability at Main Branch for all lines
        foreach ($dispatch->items as $item) {
            $inventory = BranchInventory::where('branch_id', $mainBranch->branch_id)
                ->where('product_id', $item->product_id)
                ->first();

            $onHand = $inventory ? $inventory->quantity_on_hand : 0;
            if ($onHand < $item->quantity_dispatched) {
                return response()->json([
                    'message' => "Insufficient stock at Main Branch for '{$item->product->product_name}'. On hand: {$onHand}, Requested dispatch: {$item->quantity_dispatched}."
                ], 422);
            }
        }

        DB::transaction(function () use ($dispatch, $mainBranch, $user) {
            foreach ($dispatch->items as $item) {
                $inventory = BranchInventory::where('branch_id', $mainBranch->branch_id)
                    ->where('product_id', $item->product_id)
                    ->lockForUpdate()
                    ->first();

                $oldOnHand = $inventory->quantity_on_hand;
                $inventory->quantity_on_hand -= (int) $item->quantity_dispatched;
                $inventory->save();

                // Immutable transaction ledger row
                InventoryTransaction::create([
                    'branch_id' => $mainBranch->branch_id,
                    'product_id' => $item->product_id,
                    'transaction_type' => 'transfer_dispatch',
                    'quantity' => -((int) $item->quantity_dispatched),
                    'reference_type' => 'StockDispatch',
                    'reference_id' => $dispatch->dispatch_id,
                    'reason' => "Dispatched to {$dispatch->toBranch->branch_name} (Ref: {$dispatch->reference_number})",
                    'created_by' => $user->user_id,
                ]);

                // Audit log
                AuditLog::create([
                    'user_id' => $user->user_id,
                    'user_name' => $user->full_name . ' (' . $user->role . ')',
                    'action' => 'Stock dispatched',
                    'record' => "{$item->product->product_name} (-{$item->quantity_dispatched}) · {$mainBranch->branch_name} ➔ {$dispatch->toBranch->branch_name}",
                    'entity_type' => 'StockDispatch',
                    'entity_id' => (string) $dispatch->dispatch_id,
                    'old_value' => "On hand: {$oldOnHand}",
                    'new_value' => "On hand: {$inventory->quantity_on_hand}",
                    'created_at' => now(),
                ]);
            }

            $dispatch->status = 'in_transit';
            $dispatch->sent_at = now();
            $dispatch->save();

            // Notify destination branch manager and staff
            Notification::create([
                'user_id' => null,
                'branch_id' => $dispatch->to_branch_id,
                'category' => 'Stock',
                'title' => "Incoming Stock Shipment: {$dispatch->reference_number}",
                'message' => "Main Branch has dispatched {$dispatch->items->sum('quantity_dispatched')} units to your branch. Shipment is in transit.",
                'link' => '/admin/inventory',
            ]);
        });

        return response()->json([
            'message' => "Shipment {$dispatch->reference_number} dispatched successfully! Status: In Transit.",
            'dispatch' => $dispatch->fresh(['items.product', 'toBranch']),
        ]);
    }

    /**
     * Cancel a draft dispatch.
     * POST /api/admin/dispatches/{id}/cancel
     */
    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $mainBranch = Branch::where('branch_type', 'Main')->first();
        $userBranchId = $user->branch_id ?? Branch::where('manager_id', $user->user_id)->value('branch_id');
        $isMainManager = in_array($user->role, ['Main Branch Manager']) || ($user->role === 'Branch Manager' && (int) $userBranchId === (int) $mainBranch?->branch_id);

        if (!$isMainManager && $user->role !== 'Super Admin') {
            return response()->json(['message' => 'Access denied: Only the Main Branch Manager or Super Admin can cancel stock dispatches.'], 403);
        }

        $dispatch = StockDispatch::findOrFail($id);

        if ($dispatch->status !== 'draft') {
            return response()->json(['message' => 'Only draft dispatches can be cancelled.'], 422);
        }

        $dispatch->status = 'cancelled';
        $dispatch->save();

        return response()->json(['message' => 'Dispatch cancelled successfully.']);
    }

    /**
     * Confirm receiving stock from Main Branch against a dispatch manifest.
     * Automatically adds received quantities to branch product stock and updates dispatch status.
     * POST /api/admin/dispatches/{id}/receive
     * POST /api/admin/stock-dispatches/{id}/receive
     */
    public function receive(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Rule: Super Admin cannot receive stock (must be performed by receiving branch)
        if ($user->role === 'Super Admin') {
            return response()->json([
                'message' => 'Access denied: Super Admin cannot receive stock. Stock receipts must be performed by the destination Branch Manager or Branch Staff.'
            ], 403);
        }

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

        $destBranch = Branch::findOrFail($userBranchId);

        // Main Branch distributes stock and never receives transfers
        if ($destBranch->branch_type === 'Main') {
            return response()->json([
                'message' => 'Stock Receive is only available for regular branch locations. Main Branch distributes stock and never receives transfers.'
            ], 422);
        }

        $dispatch = StockDispatch::with(['items.product.category', 'fromBranch', 'toBranch'])->findOrFail($id);

        // Receipt against dispatch addressed to another branch returns 403
        if ((int) $dispatch->to_branch_id !== (int) $destBranch->branch_id) {
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

        $itemsInput = $request->input('items', []);
        $notes = $request->input('notes', null);
        $dateReceived = $request->input('date_received') 
            ? Carbon::parse($request->input('date_received'))->toDateString() 
            : now()->toDateString();
        $refNumberPrefix = $request->input('reference_number') 
            ?: ('RCV-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4)));

        $linesToReceive = [];
        if (!empty($itemsInput) && is_array($itemsInput)) {
            foreach ($itemsInput as $inputItem) {
                $pId = (int) ($inputItem['product_id'] ?? 0);
                $qty = (int) ($inputItem['quantity_received'] ?? ($inputItem['quantity'] ?? 0));
                if ($pId > 0 && $qty > 0) {
                    $dispatchItem = $dispatch->items->firstWhere('product_id', $pId);
                    if (!$dispatchItem) {
                        return response()->json([
                            'message' => "Product ID {$pId} is not part of dispatch manifest {$dispatch->reference_number}."
                        ], 422);
                    }
                    $remaining = (int) $dispatchItem->quantity_dispatched - (int) $dispatchItem->quantity_received;
                    if ($qty > $remaining) {
                        return response()->json([
                            'message' => "Received quantity ({$qty}) exceeds remaining dispatched quantity ({$remaining}) for '{$dispatchItem->product?->product_name}'."
                        ], 422);
                    }
                    $linesToReceive[] = [
                        'dispatch_item' => $dispatchItem,
                        'product_id' => $pId,
                        'quantity' => $qty,
                    ];
                }
            }
        } else {
            // Default: receive all remaining quantities across all line items
            foreach ($dispatch->items as $dispatchItem) {
                $remaining = (int) $dispatchItem->quantity_dispatched - (int) $dispatchItem->quantity_received;
                if ($remaining > 0) {
                    $linesToReceive[] = [
                        'dispatch_item' => $dispatchItem,
                        'product_id' => $dispatchItem->product_id,
                        'quantity' => $remaining,
                    ];
                }
            }
        }

        if (empty($linesToReceive)) {
            return response()->json([
                'message' => "No remaining units to receive on dispatch manifest {$dispatch->reference_number}."
            ], 422);
        }

        $createdRecords = [];
        $totalReceived = 0;

        DB::transaction(function () use ($dispatch, $destBranch, $linesToReceive, $dateReceived, $refNumberPrefix, $notes, $user, &$createdRecords, &$totalReceived) {
            foreach ($linesToReceive as $line) {
                $dispatchItem = $line['dispatch_item'];
                $qty = $line['quantity'];
                $product = $dispatchItem->product;
                $categoryName = $product?->category?->category_name ?? 'Bakery';

                // 1. Create ReceivedStock record
                $record = ReceivedStock::create([
                    'branch_id' => $destBranch->branch_id,
                    'dispatch_id' => $dispatch->dispatch_id,
                    'product_id' => $product->product_id,
                    'product_name' => $product->product_name,
                    'category_name' => $categoryName,
                    'quantity_received' => $qty,
                    'date_received' => $dateReceived,
                    'received_by' => $user->user_id,
                    'receiver_name' => $user->full_name,
                    'reference_number' => $refNumberPrefix,
                    'notes' => $notes,
                ]);
                $createdRecords[] = $record;
                $totalReceived += $qty;

                // 2. Increment destination branch product inventory
                $inventory = BranchInventory::firstOrCreate(
                    [
                        'branch_id' => $destBranch->branch_id,
                        'product_id' => $product->product_id,
                    ],
                    [
                        'quantity_on_hand' => 0,
                        'reserved_quantity' => 0,
                        'reorder_level' => 5,
                    ]
                );

                $oldOnHand = $inventory->quantity_on_hand;
                $inventory->quantity_on_hand += $qty;
                $inventory->save();

                // 3. Update dispatch item received counter
                $dispatchItem->quantity_received += $qty;
                $dispatchItem->save();

                // 4. Immutable inventory transaction ledger row
                InventoryTransaction::create([
                    'branch_id' => $destBranch->branch_id,
                    'product_id' => $product->product_id,
                    'transaction_type' => 'transfer_receive',
                    'quantity' => $qty,
                    'reference_type' => 'StockDispatch',
                    'reference_id' => $dispatch->dispatch_id,
                    'reason' => "Stock received from {$dispatch->fromBranch->branch_name} (Manifest: {$dispatch->reference_number})",
                    'created_by' => $user->user_id,
                ]);

                // 5. Audit log
                AuditLog::create([
                    'user_id' => $user->user_id,
                    'user_name' => $user->full_name . ' (' . $user->role . ')',
                    'action' => 'Stock received against dispatch',
                    'record' => "{$product->product_name} (+{$qty}) · Manifest {$dispatch->reference_number} at {$destBranch->branch_name}",
                    'entity_type' => 'StockDispatch',
                    'entity_id' => (string) $dispatch->dispatch_id,
                    'old_value' => "On hand: {$oldOnHand}",
                    'new_value' => "On hand: {$inventory->quantity_on_hand} (+{$qty} units)",
                    'created_at' => now(),
                ]);
            }

            // 6. Update dispatch status
            $allComplete = true;
            foreach ($dispatch->items()->get() as $item) {
                if ($item->quantity_received < $item->quantity_dispatched) {
                    $allComplete = false;
                }
            }

            $dispatch->status = $allComplete ? 'received' : 'partially_received';
            $dispatch->save();

            // 7. Notification to branch staff
            Notification::create([
                'user_id' => null,
                'branch_id' => $destBranch->branch_id,
                'category' => 'Stock',
                'title' => "Shipment Received: {$dispatch->reference_number}",
                'message' => "Successfully confirmed receipt of {$totalReceived} units from Main Branch (Manifest {$dispatch->reference_number}). Stock updated.",
                'link' => '/admin/inventory',
            ]);

            // 8. Notification to Main Branch Manager
            Notification::create([
                'user_id' => null,
                'branch_id' => $dispatch->from_branch_id,
                'category' => 'Stock',
                'title' => "Shipment Confirmed by {$destBranch->branch_name}",
                'message' => "{$destBranch->branch_name} confirmed receipt of {$totalReceived} units on manifest {$dispatch->reference_number}. Status: {$dispatch->status}.",
                'link' => '/admin/distribution',
            ]);
        });

        return response()->json([
            'message' => "Successfully received {$totalReceived} units on manifest {$dispatch->reference_number}! Available stock updated.",
            'dispatch_id' => $dispatch->dispatch_id,
            'reference_number' => $dispatch->reference_number,
            'status' => $dispatch->status,
            'total_units_received' => $totalReceived,
            'received_records' => $createdRecords,
        ]);
    }
}
