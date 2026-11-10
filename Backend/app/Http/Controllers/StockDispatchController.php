<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StockDispatch;
use App\Models\StockDispatchItem;
use App\Models\Branch;
use App\Models\Product;
use App\Models\BranchInventory;
use App\Models\InventoryTransaction;
use App\Models\AuditLog;
use App\Models\Notification;

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
        $dispatch = StockDispatch::findOrFail($id);

        if ($dispatch->status !== 'draft') {
            return response()->json(['message' => 'Only draft dispatches can be cancelled.'], 422);
        }

        $dispatch->status = 'cancelled';
        $dispatch->save();

        return response()->json(['message' => 'Dispatch cancelled successfully.']);
    }
}
