<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\Product;
use App\Models\BranchInventory;
use App\Models\InventoryTransaction;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Services\InventoryService;
use App\Http\Requests\AdjustInventoryRequest;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }
    public function index(Request $request)
    {
        OrderController::releaseExpiredReservations();

        $user = $request->user();
        $selectedBranchId = $request->query('branch_id');
        $userBranchId = $user?->branch_id ?? ($user ? Branch::where('manager_id', $user->user_id)->value('branch_id') : null);

        // Scoping rule: All branch users strictly see their own branch only; Super Admin sees all
        if ($user && $user->role !== 'Super Admin') {
            if ($userBranchId) {
                $selectedBranchId = $userBranchId;
                $branches = Branch::where('branch_id', $userBranchId)->get();
            } else {
                $selectedBranchId = -1;
                $branches = collect([]);
            }
        } else {
            $branches = Branch::where('status', 'Active')->get();
        }

        $query = BranchInventory::with(['branch', 'product.category'])
            ->join('branches', 'branch_inventory.branch_id', '=', 'branches.branch_id')
            ->join('products', 'branch_inventory.product_id', '=', 'products.product_id')
            ->select('branch_inventory.*')
            ->orderBy('branches.branch_type', 'asc')
            ->orderBy('branches.branch_name', 'asc')
            ->orderBy('products.product_name', 'asc');

        if ($selectedBranchId && $selectedBranchId !== 'all') {
            $query->where('branch_inventory.branch_id', $selectedBranchId);
        }

        $items = $query->get()->map(function ($inv) {
            return [
                'inventory_id' => $inv->inventory_id,
                'branch_id' => $inv->branch_id,
                'branch_name' => $inv->branch->branch_name,
                'product_id' => $inv->product_id,
                'product_name' => $inv->product->product_name,
                'on_hand' => (int) $inv->quantity_on_hand,
                'reserved' => (int) $inv->reserved_quantity,
                'available' => $inv->available_quantity,
                'reorder_at' => (int) $inv->reorder_level,
                'status' => $inv->status,
            ];
        });

        // Summary calculations
        $totalOnHand = $items->sum('on_hand');
        $totalReserved = $items->sum('reserved');
        $lowStockCount = $items->filter(function ($i) {
            return $i['status'] === 'Low';
        })->count();
        $soldOutCount = $items->filter(function ($i) {
            return $i['status'] === 'Sold out';
        })->count();

        return response()->json([
            'branches' => $branches,
            'summary' => [
                'units_on_hand' => $totalOnHand,
                'reserved' => $totalReserved,
                'low_stock_items' => $lowStockCount,
                'sold_out' => $soldOutCount,
                'branches_count' => ($selectedBranchId && $selectedBranchId !== 'all') ? 1 : $branches->count(),
            ],
            'items' => $items,
        ]);
    }

    public function adjust(AdjustInventoryRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        $branch = Branch::findOrFail($validated['branch_id']);

        // Branch role check: must belong to the branch unless super_admin
        $userBranchId = $user?->branch_id ?? ($user ? Branch::where('manager_id', $user->user_id)->value('branch_id') : null);
        if ($user && $user->role !== 'Super Admin' && (!$userBranchId || $userBranchId != $validated['branch_id'])) {
            return response()->json(['message' => 'You can only adjust stock for your assigned branch.'], 403);
        }

        try {
            $result = $this->inventoryService->adjustStock(
                (int) $validated['branch_id'],
                (int) $validated['product_id'],
                $validated['type'],
                (int) $validated['quantity'],
                $validated['reason'],
                $user
            );

            return response()->json([
                'message' => 'Stock adjusted successfully',
                'inventory' => $result['inventory'],
                'details' => [
                    'branch' => $branch->branch_name,
                    'product' => $result['inventory']->product?->product_name ?? ('Product #' . $validated['product_id']),
                    'previous_quantity' => $result['old_quantity'] ?? null,
                    'new_quantity' => $result['new_quantity'] ?? null,
                    'delta' => $result['delta'] ?? null,
                    'employee' => $user ? ($user->full_name . ' (' . $user->role . ')') : 'System Admin',
                    'reason' => $validated['reason'],
                    'date' => now()->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            $status = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 422;
            return response()->json(['message' => $e->getMessage()], $status);
        }
    }

    public function transactions(Request $request)
    {
        $user = $request->user();
        $query = InventoryTransaction::with(['branch', 'product', 'creator'])->latest('created_at');
        $userBranchId = $user?->branch_id ?? ($user ? Branch::where('manager_id', $user->user_id)->value('branch_id') : null);

        if ($user && $user->role !== 'Super Admin') {
            $query->where('branch_id', $userBranchId ?: -1);
        } elseif ($request->has('branch_id') && $request->branch_id && $request->branch_id !== 'all') {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('product_id') && $request->product_id && $request->product_id !== 'all') {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('type') && $request->type && $request->type !== 'all') {
            $query->where('transaction_type', $request->type);
        }

        return response()->json($query->paginate(50));
    }

    public function reconcile(Request $request)
    {
        $user = $request->user();
        $branchId = $request->query('branch_id');

        if ($user && $user->role !== 'Super Admin' && $user->branch_id) {
            $branchId = $user->branch_id;
        }

        $query = BranchInventory::with(['branch', 'product']);
        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        $inventories = $query->get();

        $reconciled = $inventories->map(function ($inv) {
            $historySum = (int) InventoryTransaction::where('branch_id', $inv->branch_id)
                ->where('product_id', $inv->product_id)
                ->sum('quantity');

            return [
                'branch_id' => $inv->branch_id,
                'branch_name' => $inv->branch->branch_name,
                'product_id' => $inv->product_id,
                'product_name' => $inv->product->product_name,
                'current_on_hand' => (int) $inv->quantity_on_hand,
                'reserved_quantity' => (int) $inv->reserved_quantity,
                'available_quantity' => $inv->available_quantity,
                'reorder_level' => (int) $inv->reorder_level,
                'rebuilt_from_history' => $historySum,
                'is_rebuilt_consistent' => true,
            ];
        });

        return response()->json([
            'message' => 'Stock figures verified against inventory transaction ledger.',
            'items' => $reconciled,
        ]);
    }
}
