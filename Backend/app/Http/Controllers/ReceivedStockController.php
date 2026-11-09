<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ReceivedStock;
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

        // Check if target branch is Main Branch
        $branch = $branchId ? Branch::find($branchId) : null;
        $isMainBranch = $branch && ($branch->branch_type === 'Main' || $branch->branch_id === 1);

        $query = ReceivedStock::with(['product.category', 'receiver'])
            ->orderBy('date_received', 'desc')
            ->orderBy('received_stock_id', 'desc');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        } elseif (!$isSuperAdmin) {
            return response()->json([
                'received_stocks' => [],
                'latest_received' => null,
                'is_main_branch' => false,
            ]);
        }

        $records = $query->take(100)->get()->map(function ($r) {
            return [
                'received_stock_id' => $r->received_stock_id,
                'branch_id' => $r->branch_id,
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

        return response()->json([
            'received_stocks' => $records,
            'latest_received' => $latest,
            'branch_id' => $branchId,
            'branch_name' => $branch?->branch_name,
            'is_main_branch' => $isMainBranch,
            'total_received_units' => (int) $records->sum('quantity_received'),
        ]);
    }

    /**
     * Store a new stock receive record for a branch.
     * Updates branch inventory and records transaction ledger & audit log.
     * Blocked for Main Branch.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $isSuperAdmin = ($user->role === 'Super Admin');

        $validated = $request->validate([
            'product_id' => 'required|exists:products,product_id',
            'quantity_received' => 'required|integer|min:1|max:100000',
            'date_received' => 'nullable|date',
            'branch_id' => 'nullable|exists:branches,branch_id',
            'notes' => 'nullable|string|max:500',
            'reference_number' => 'nullable|string|max:100',
        ]);

        $branchId = null;
        if ($isSuperAdmin) {
            $branchId = $validated['branch_id'] ?? null;
            if (!$branchId) {
                return response()->json(['message' => 'Please select a destination branch for receiving stock.'], 422);
            }
        } else {
            $branchId = $user->branch_id ?? Branch::where('manager_id', $user->user_id)->value('branch_id');
            if (!$branchId) {
                return response()->json(['message' => 'Your account is not assigned to a valid branch.'], 403);
            }
        }

        $branch = Branch::findOrFail($branchId);

        // Rule 3: Main Branch: Do not allow receiving stock in the Main Branch
        if ($branch->branch_type === 'Main' || $branch->branch_id === 1) {
            return response()->json([
                'message' => 'Stock Receive is only available for branch locations. Main Branch distributes stock and does not receive transfers.'
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

        $receivedRecord = DB::transaction(function () use ($branch, $product, $categoryName, $validated, $dateReceived, $refNumber, $user) {
            // 1. Create ReceivedStock record
            $record = ReceivedStock::create([
                'branch_id' => $branch->branch_id,
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

            // 2. Update or create branch inventory
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

            // 3. Record InventoryTransaction
            InventoryTransaction::create([
                'branch_id' => $branch->branch_id,
                'product_id' => $product->product_id,
                'transaction_type' => 'transfer_receive',
                'quantity' => (int) $validated['quantity_received'],
                'reference_type' => 'ReceivedStock',
                'reference_id' => $record->received_stock_id,
                'reason' => "Stock Receive: {$validated['quantity_received']} units of {$product->product_name} ({$categoryName}) at {$branch->branch_name}",
                'created_by' => $user->user_id,
            ]);

            // 4. Record AuditLog
            AuditLog::create([
                'user_id' => $user->user_id,
                'user_name' => $user->full_name . ' (' . $user->role . ')',
                'action' => 'Stock received',
                'record' => "{$product->product_name} (+{$validated['quantity_received']}) · {$branch->branch_name}",
                'entity_type' => 'ReceivedStock',
                'entity_id' => (string) $record->received_stock_id,
                'old_value' => "On hand: {$oldOnHand}",
                'new_value' => "On hand: {$inventory->quantity_on_hand} (+{$validated['quantity_received']} units)",
                'created_at' => now(),
            ]);

            // 5. Send Branch Notification
            Notification::create([
                'user_id' => null,
                'branch_id' => $branch->branch_id,
                'category' => 'Stock',
                'title' => "Stock Received: {$product->product_name}",
                'message' => "Received +{$validated['quantity_received']} units of {$product->product_name} ({$categoryName}) at {$branch->branch_name}. Available: {$inventory->available_quantity}.",
                'link' => '/admin/inventory',
            ]);

            return $record;
        });

        $formatted = [
            'received_stock_id' => $receivedRecord->received_stock_id,
            'branch_id' => $receivedRecord->branch_id,
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
            'message' => "Successfully received {$validated['quantity_received']} units of {$product->product_name} at {$branch->branch_name}!",
            'received_stock' => $formatted,
            'latest_received' => $formatted,
        ], 201);
    }
}
