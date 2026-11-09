<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\Product;
use App\Models\BranchInventory;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\StockRequest;
use App\Models\StockRequestItem;
use App\Models\InventoryTransaction;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\AppSetting;
use Carbon\Carbon;

class TransferController extends Controller
{
    /**
     * Generate next strictly unique transfer number in format TRF-YYYY-XXXX
     */
    private function generateTransferNumber(): string
    {
        $year = date('Y');
        $lastTransfer = StockTransfer::where('transfer_number', 'like', "TRF-{$year}-%")
            ->orderBy('transfer_id', 'desc')
            ->first();

        $seq = 1;
        if ($lastTransfer && preg_match('/TRF-\d{4}-(\d+)/', $lastTransfer->transfer_number, $matches)) {
            $seq = intval($matches[1]) + 1;
        }

        do {
            $transferNumber = sprintf('TRF-%s-%04d', $year, $seq);
            $exists = StockTransfer::where('transfer_number', $transferNumber)->exists();
            if ($exists) {
                $seq++;
            }
        } while ($exists);

        return $transferNumber;
    }

    /**
     * Distribution Dashboard with real database metrics and per-branch status
     */
    public function dashboard(Request $request)
    {
        $mainBranch = Branch::where('branch_type', 'Main')->first();
        $mainBranchStock = 0;
        if ($mainBranch) {
            $mainBranchStock = (int) BranchInventory::where('branch_id', $mainBranch->branch_id)->sum('quantity_on_hand');
        }

        // Transfers metrics
        $today = Carbon::today();
        $week = Carbon::now()->startOfWeek();
        $month = Carbon::now()->startOfMonth();

        $transfersToday = StockTransfer::whereDate('dispatched_at', $today)->get();
        $transfersWeek = StockTransfer::where('dispatched_at', '>=', $week)->get();
        $transfersMonth = StockTransfer::where('dispatched_at', '>=', $month)->get();

        $sentTodayCount = $transfersToday->count();
        $sentTodayUnits = (int) StockTransferItem::whereIn('transfer_id', $transfersToday->pluck('transfer_id'))->sum('quantity_sent');

        $sentWeekCount = $transfersWeek->count();
        $sentWeekUnits = (int) StockTransferItem::whereIn('transfer_id', $transfersWeek->pluck('transfer_id'))->sum('quantity_sent');

        $sentMonthCount = $transfersMonth->count();
        $sentMonthUnits = (int) StockTransferItem::whereIn('transfer_id', $transfersMonth->pluck('transfer_id'))->sum('quantity_sent');

        $awaitingDispatch = StockTransfer::whereIn('status', ['Draft', 'Approved'])->count();
        
        $inTransitTransfers = StockTransfer::whereIn('status', ['Dispatched', 'In transit'])->get();
        $inTransitUnits = (int) StockTransferItem::whereIn('transfer_id', $inTransitTransfers->pluck('transfer_id'))->sum('quantity_sent');
        $inTransitNote = $inTransitTransfers->first() ? "{$inTransitTransfers->first()->transfer_number} to {$inTransitTransfers->first()->destinationBranch?->branch_name}" : 'None';

        $receivedTransfers = StockTransfer::where('status', 'Received')->get();
        $receivedUnits = (int) StockTransferItem::whereIn('transfer_id', $receivedTransfers->pluck('transfer_id'))->sum('quantity_received');

        $disputedTransfers = StockTransfer::where('status', 'Disputed')->get();
        $missingUnits = (int) StockTransferItem::whereIn('transfer_id', $disputedTransfers->pluck('transfer_id'))->sum('missing_quantity');
        $damagedUnits = (int) StockTransferItem::whereIn('transfer_id', $disputedTransfers->pluck('transfer_id'))->sum('damaged_quantity');
        $disputeNote = $disputedTransfers->first() ? "{$disputedTransfers->first()->transfer_number} disputed" : 'None';

        // Check for overdue transfers (> 24 hours unreceived) and emit notifications
        $unreceivedHours = (int) AppSetting::get('unreceived_reminder_hours', 24);
        $overdueTransfers = StockTransfer::whereIn('status', ['Dispatched', 'In transit'])
            ->whereNotNull('dispatched_at')
            ->where('dispatched_at', '<=', Carbon::now()->subHours($unreceivedHours))
            ->get();

        foreach ($overdueTransfers as $overdue) {
            $existing = Notification::where('category', 'Transfers')
                ->where('message', 'like', "%{$overdue->transfer_number}%")
                ->where('created_at', '>=', Carbon::now()->subHours($unreceivedHours))
                ->first();

            if (!$existing) {
                Notification::create([
                    'user_id' => null,
                    'branch_id' => $overdue->to_branch_id,
                    'category' => 'Transfers',
                    'title' => 'Transfer not received',
                    'message' => "{$overdue->transfer_number} to {$overdue->destinationBranch?->branch_name} unreceived after {$unreceivedHours} hours.",
                    'link' => '/admin/distribution',
                ]);
            }
        }

        // Latest transfer per branch
        $branches = Branch::where('status', 'Active')->get();
        $latestPerBranch = [];

        foreach ($branches as $branch) {
            $latest = StockTransfer::where('destination_branch_id', $branch->branch_id)
                ->with(['items.product', 'destinationBranch', 'sourceBranch'])
                ->latest('updated_at')
                ->first();

            if ($latest) {
                $totalSent = (int) $latest->items->sum('quantity_sent');
                $totalReceived = (int) $latest->items->sum('quantity_received');
                $totalMissing = (int) $latest->items->sum('missing_quantity');
                $totalDamaged = (int) $latest->items->sum('damaged_quantity');

                $action = 'View';
                if ($latest->status === 'Disputed') {
                    $action = 'Resolve';
                } elseif ($latest->status === 'In transit' || $latest->status === 'Dispatched') {
                    $action = 'Receive';
                } elseif ($latest->status === 'Approved') {
                    $action = 'Dispatch';
                } elseif ($latest->status === 'Draft') {
                    $action = 'Approve';
                }

                $latestPerBranch[] = [
                    'transfer_id' => $latest->transfer_id,
                    'branch' => $branch->branch_name,
                    'branch_id' => $branch->branch_id,
                    'transfer' => $latest->transfer_number,
                    'sent' => $totalSent,
                    'received' => $totalReceived,
                    'missing' => $totalMissing,
                    'damaged' => $totalDamaged,
                    'status' => $latest->status,
                    'action' => $action,
                    'notes' => $latest->notes,
                    'dispatched_at' => $latest->dispatched_at ? $latest->dispatched_at->format('M d, H:i') : null,
                    'items' => $latest->items,
                ];
            }
        }

        // Pending Stock requests
        $stockRequests = StockRequest::where('status', 'Pending')
            ->with(['branch', 'items.product', 'requester'])
            ->get()
            ->map(function ($req) {
                return [
                    'request_id' => $req->request_id,
                    'branch_id' => $req->branch_id,
                    'branch' => $req->branch?->branch_name ?? 'Branch',
                    'product_id' => $req->items->first()?->product_id,
                    'product' => $req->items->first()?->product?->product_name ?? 'Products',
                    'qty' => $req->items->sum('quantity_requested'),
                    'requested' => $req->created_at ? $req->created_at->format('M d, H:i') : 'Today',
                    'status' => $req->status,
                ];
            });

        return response()->json([
            'main_branch_stock' => $mainBranchStock,
            'sent_today_units' => $sentTodayUnits,
            'sent_today_count' => $sentTodayCount,
            'sent_week_units' => $sentWeekUnits,
            'sent_week_count' => $sentWeekCount,
            'sent_month_units' => $sentMonthUnits,
            'sent_month_count' => $sentMonthCount,
            'awaiting_dispatch' => $awaitingDispatch,
            'in_transit_units' => $inTransitUnits,
            'in_transit_note' => $inTransitNote,
            'received_units' => $receivedUnits,
            'missing_units' => $missingUnits,
            'damaged_units' => $damagedUnits,
            'dispute_note' => $disputeNote,
            'latest_transfers' => $latestPerBranch,
            'stock_requests' => $stockRequests,
        ]);
    }

    /**
     * List transfers with optional role scoping and status filtering
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = StockTransfer::with(['sourceBranch', 'destinationBranch', 'items.product', 'creator', 'approver'])
            ->latest('created_at');

        // Scoping: Non-super-admin, non-main-manager strictly restricted to transfers concerning their branch
        if ($user && $user->role !== 'Super Admin' && $user->role !== 'Main Branch Manager' && $user->branch_id) {
            $query->where(function ($q) use ($user) {
                $q->where('destination_branch_id', $user->branch_id)
                  ->orWhere('source_branch_id', $user->branch_id);
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        return response()->json($query->get());
    }

    /**
     * Show single transfer details
     */
    public function show(Request $request, $id)
    {
        $transfer = StockTransfer::with(['sourceBranch', 'destinationBranch', 'items.product', 'creator', 'approver'])
            ->findOrFail($id);

        $user = $request->user();
        if ($user && $user->role !== 'Super Admin' && $user->role !== 'Main Branch Manager' && $user->branch_id) {
            if ($transfer->destination_branch_id != $user->branch_id && $transfer->source_branch_id != $user->branch_id) {
                return response()->json(['message' => 'Access denied: You can only view transfers concerning your assigned branch.'], 403);
            }
        }

        return response()->json($transfer);
    }

    /**
     * Create a transfer (Main Manager or Super Admin)
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'destination_branch_id' => 'required|exists:branches,branch_id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.quantity_sent' => 'required|integer|min:1',
            'status' => 'sometimes|in:Draft,Approved',
        ]);

        $mainBranch = Branch::where('branch_type', 'Main')->firstOrFail();
        if ($validated['destination_branch_id'] == $mainBranch->branch_id) {
            return response()->json(['message' => 'Destination cannot be the main branch.'], 422);
        }

        $transferNumber = $this->generateTransferNumber();

        $threshold = (int) AppSetting::get('approval_threshold_units', 200);
        $totalUnits = collect($validated['items'])->sum('quantity_sent');

        // Rule: Super Admin approval required if over threshold or if explicitly forced
        $initialStatus = 'Draft';
        if (isset($validated['status']) && $validated['status'] === 'Draft') {
            $initialStatus = 'Draft';
        } elseif ($totalUnits <= $threshold && AppSetting::get('transfer_approval_required', '0') !== '1') {
            $initialStatus = 'Approved';
        }

        $transfer = DB::transaction(function () use ($validated, $mainBranch, $transferNumber, $initialStatus, $user) {
            $transfer = StockTransfer::create([
                'transfer_number' => $transferNumber,
                'source_branch_id' => $mainBranch->branch_id,
                'destination_branch_id' => $validated['destination_branch_id'],
                'status' => $initialStatus,
                'created_by' => $user?->user_id ?? 1,
                'approved_by' => ($initialStatus === 'Approved') ? ($user?->user_id ?? 1) : null,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                StockTransferItem::create([
                    'transfer_id' => $transfer->transfer_id,
                    'product_id' => $item['product_id'],
                    'quantity_sent' => $item['quantity_sent'],
                    'quantity_received' => 0,
                    'damaged_quantity' => 0,
                    'missing_quantity' => 0,
                ]);
            }

            AuditLog::create([
                'user_id' => $user?->user_id,
                'user_name' => $user ? ($user->full_name . ' (' . $user->role . ')') : 'Main Manager',
                'action' => 'Transfer created',
                'record' => $transferNumber,
                'entity_type' => 'StockTransfer',
                'entity_id' => (string) $transfer->transfer_id,
                'old_value' => '—',
                'new_value' => $initialStatus,
                'created_at' => now(),
            ]);

            return $transfer;
        });

        return response()->json($transfer->load(['items.product', 'destinationBranch']), 201);
    }

    /**
     * Approve transfer (Super Admin only)
     * Allowed transition: Draft -> Approved
     */
    public function approve(Request $request, $id)
    {
        $transfer = StockTransfer::findOrFail($id);

        if ($transfer->status !== 'Draft') {
            return response()->json(['message' => 'Only Draft transfers can be approved.'], 422);
        }

        $transfer->status = 'Approved';
        $transfer->approved_by = $request->user()?->user_id ?? 1;
        $transfer->save();

        AuditLog::create([
            'user_id' => $request->user()?->user_id,
            'user_name' => $request->user()?->full_name ?? 'Super Admin',
            'action' => 'Transfer approved',
            'record' => $transfer->transfer_number,
            'entity_type' => 'StockTransfer',
            'entity_id' => (string) $transfer->transfer_id,
            'old_value' => 'Draft',
            'new_value' => 'Approved',
            'created_at' => now(),
        ]);

        return response()->json($transfer);
    }

    /**
     * Dispatch transfer (Main Manager or Super Admin)
     * Allowed transition: Approved -> In transit (Dispatched)
     * Rules:
     * - Sent quantity cannot exceed source on-hand stock at dispatch time.
     * - Source on hand drops immediately; units become In transit.
     * - Destination available stock is NOT increased.
     */
    public function dispatch(Request $request, $id)
    {
        $transfer = StockTransfer::with(['items.product', 'destinationBranch'])->findOrFail($id);

        if ($transfer->status !== 'Approved') {
            return response()->json(['message' => 'Transfer must be Approved before dispatch. Current status: ' . $transfer->status], 422);
        }

        DB::transaction(function () use ($transfer, $request) {
            foreach ($transfer->items as $item) {
                $mainInventory = BranchInventory::where('branch_id', $transfer->source_branch_id)
                    ->where('product_id', $item->product_id)
                    ->lockForUpdate()
                    ->first();

                if (!$mainInventory || $mainInventory->quantity_on_hand < $item->quantity_sent) {
                    $prodName = $item->product?->product_name ?? "Product #{$item->product_id}";
                    $avail = $mainInventory?->quantity_on_hand ?? 0;
                    abort(422, "Sent quantity ({$item->quantity_sent}) cannot exceed Main Branch on hand ({$avail}) at dispatch time for {$prodName}.");
                }

                $mainInventory->quantity_on_hand -= $item->quantity_sent;
                $mainInventory->save();

                InventoryTransaction::create([
                    'branch_id' => $transfer->source_branch_id,
                    'product_id' => $item->product_id,
                    'transaction_type' => 'transfer_dispatch',
                    'quantity' => -$item->quantity_sent,
                    'reference_type' => 'StockTransfer',
                    'reference_id' => $transfer->transfer_id,
                    'reason' => "Dispatched to {$transfer->destinationBranch?->branch_name} ({$transfer->transfer_number})",
                    'created_by' => $request->user()?->user_id,
                ]);
            }

            $transfer->status = 'In transit';
            $transfer->dispatched_at = now();
            $transfer->save();

            AuditLog::create([
                'user_id' => $request->user()?->user_id,
                'user_name' => $request->user() ? ($request->user()->full_name . ' (' . $request->user()->role . ')') : 'Main Manager',
                'action' => 'Transfer dispatched',
                'record' => $transfer->transfer_number,
                'entity_type' => 'StockTransfer',
                'entity_id' => (string) $transfer->transfer_id,
                'old_value' => 'Approved',
                'new_value' => 'In transit',
                'created_at' => now(),
            ]);
        });

        return response()->json($transfer->fresh(['items.product', 'destinationBranch']));
    }

    /**
     * Receive transfer at destination branch
     * Allowed transition: In transit / Dispatched / Partially received -> Received / Partially received / Disputed
     * Rules:
     * - Who sets it: destination branch staff/admin or Super Admin (Main Manager cannot confirm).
     * - Strict Rule: For each line, received + damaged + missing must equal sent. System blocks saving until it does.
     * - Only confirmed received quantity is added to destination on hand.
     * - Any difference creates a discrepancy with mandatory reason and notifies Super Admin and Main Manager.
     */
    public function receive(Request $request, $id)
    {
        $transfer = StockTransfer::with(['items.product', 'destinationBranch', 'sourceBranch'])->findOrFail($id);
        $user = $request->user();

        // Permission check: destination branch staff/manager or Super Admin
        if ($user && $user->role !== 'Super Admin' && $user->branch_id && $user->branch_id != $transfer->destination_branch_id) {
            return response()->json(['message' => 'You can only confirm receipts for transfers sent to your assigned branch.'], 403);
        }
        if ($user && $user->role === 'Main Branch Manager' && $transfer->destination_branch_id != $user->branch_id) {
            return response()->json(['message' => 'Main Branch Manager cannot confirm receipts for other branches. Destination branch staff must count and confirm.'], 403);
        }

        if (!in_array($transfer->status, ['Dispatched', 'In transit', 'Partially received'])) {
            return response()->json(['message' => 'Only in-transit or partially received transfers can be received. Current status: ' . $transfer->status], 422);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.transfer_item_id' => 'required|exists:stock_transfer_items,transfer_item_id',
            'items.*.quantity_received' => 'required|integer|min:0',
            'items.*.damaged_quantity' => 'required|integer|min:0',
            'items.*.missing_quantity' => 'required|integer|min:0',
            'items.*.discrepancy_reason' => 'nullable|string',
        ]);

        $hasDiscrepancy = false;
        $totalSent = 0;
        $totalAccounted = 0;

        DB::transaction(function () use ($transfer, $validated, $request, &$hasDiscrepancy, &$totalSent, &$totalAccounted) {
            foreach ($validated['items'] as $itemData) {
                $item = StockTransferItem::with('product')->findOrFail($itemData['transfer_item_id']);

                $received = (int) $itemData['quantity_received'];
                $damaged = (int) $itemData['damaged_quantity'];
                $missing = (int) $itemData['missing_quantity'];

                // Strict Rule: received + damaged + missing must equal sent
                if (($received + $damaged + $missing) !== (int) $item->quantity_sent) {
                    $prodName = $item->product?->product_name ?? "Line item";
                    $sum = $received + $damaged + $missing;
                    abort(422, "For line '{$prodName}', received ({$received}) + damaged ({$damaged}) + missing ({$missing}) = {$sum}, which must equal sent ({$item->quantity_sent}). System blocks saving until it does.");
                }

                if ($damaged > 0 || $missing > 0) {
                    $hasDiscrepancy = true;
                    if (empty($itemData['discrepancy_reason'])) {
                        abort(422, "A discrepancy reason is mandatory when received quantity differs from sent.");
                    }
                }

                $totalSent += (int) $item->quantity_sent;
                $totalAccounted += ($received + $damaged + $missing);

                $item->update([
                    'quantity_received' => $received,
                    'damaged_quantity' => $damaged,
                    'missing_quantity' => $missing,
                    'discrepancy_reason' => $itemData['discrepancy_reason'] ?? null,
                ]);

                // Increase destination available stock only by confirmed received quantity
                if ($received > 0) {
                    $destInventory = BranchInventory::firstOrCreate(
                        ['branch_id' => $transfer->destination_branch_id, 'product_id' => $item->product_id],
                        ['quantity_on_hand' => 0, 'reserved_quantity' => 0, 'reorder_level' => 10]
                    );

                    $destInventory->quantity_on_hand += $received;
                    $destInventory->save();

                    InventoryTransaction::create([
                        'branch_id' => $transfer->destination_branch_id,
                        'product_id' => $item->product_id,
                        'transaction_type' => 'transfer_receive',
                        'quantity' => $received,
                        'reference_type' => 'StockTransfer',
                        'reference_id' => $transfer->transfer_id,
                        'reason' => "Received from {$transfer->sourceBranch?->branch_name} ({$transfer->transfer_number})",
                        'created_by' => $request->user()?->user_id,
                    ]);
                }
            }

            // Assign status: Partially received, Disputed, or Received
            $newStatus = 'Received';
            if ($hasDiscrepancy) {
                $newStatus = 'Disputed';
            } elseif ($totalAccounted < $totalSent) {
                $newStatus = 'Partially received';
            }

            $transfer->status = $newStatus;
            $transfer->received_at = now();
            $transfer->save();

            // Create notification if disputed
            if ($hasDiscrepancy) {
                Notification::create([
                    'user_id' => null,
                    'branch_id' => $transfer->to_branch_id,
                    'category' => 'Transfers',
                    'title' => 'Transfer disputed',
                    'message' => "{$transfer->transfer_number}: Received with differences at {$transfer->destinationBranch?->branch_name}. Reason needed to resolve.",
                    'link' => '/admin/distribution',
                ]);
            }

            AuditLog::create([
                'user_id' => $request->user()?->user_id,
                'user_name' => $request->user() ? ($request->user()->full_name . ' (' . $request->user()->role . ')') : 'Branch Admin',
                'action' => 'Transfer received',
                'record' => $transfer->transfer_number,
                'entity_type' => 'StockTransfer',
                'entity_id' => (string) $transfer->transfer_id,
                'old_value' => 'In transit',
                'new_value' => "{$newStatus}" . ($hasDiscrepancy ? ' (Discrepancy reported)' : ''),
                'created_at' => now(),
            ]);
        });

        return response()->json($transfer->fresh(['items.product', 'destinationBranch', 'sourceBranch']));
    }

    /**
     * Resolve transfer dispute (Super Admin or Main Manager)
     * Allowed transition: Disputed -> Received after resolution
     * Resolutions:
     * - written_off: write off missing/damaged units
     * - found: missing items located and added to destination stock
     * - re_sent: replacement transfer created for missing/damaged units
     */
    public function resolve(Request $request, $id)
    {
        $transfer = StockTransfer::with(['items.product', 'destinationBranch', 'sourceBranch'])->findOrFail($id);
        $user = $request->user();

        if ($transfer->status !== 'Disputed') {
            return response()->json(['message' => 'Only disputed transfers can be resolved.'], 422);
        }

        $validated = $request->validate([
            'resolution' => 'required|in:written_off,found,re_sent',
            'notes' => 'required|string',
        ]);

        DB::transaction(function () use ($transfer, $validated, $user) {
            if ($validated['resolution'] === 'found') {
                // Located items: add missing quantity into destination branch inventory
                foreach ($transfer->items as $item) {
                    if ($item->missing_quantity > 0) {
                        $destInventory = BranchInventory::firstOrCreate(
                            ['branch_id' => $transfer->destination_branch_id, 'product_id' => $item->product_id],
                            ['quantity_on_hand' => 0, 'reserved_quantity' => 0, 'reorder_level' => 10]
                        );
                        $destInventory->quantity_on_hand += $item->missing_quantity;
                        $destInventory->save();

                        InventoryTransaction::create([
                            'branch_id' => $transfer->destination_branch_id,
                            'product_id' => $item->product_id,
                            'transaction_type' => 'transfer_receive',
                            'quantity' => $item->missing_quantity,
                            'reference_type' => 'StockTransfer',
                            'reference_id' => $transfer->transfer_id,
                            'reason' => "Dispute resolved: {$item->missing_quantity} units found and added to stock ({$validated['notes']})",
                            'created_by' => $user?->user_id,
                        ]);
                    }
                }
            } elseif ($validated['resolution'] === 're_sent') {
                // Create a replacement transfer for the missing/damaged items
                $replacementNumber = $this->generateTransferNumber();

                $replacement = StockTransfer::create([
                    'transfer_number' => $replacementNumber,
                    'source_branch_id' => $transfer->source_branch_id,
                    'destination_branch_id' => $transfer->destination_branch_id,
                    'status' => 'Approved',
                    'created_by' => $user?->user_id ?? 1,
                    'approved_by' => $user?->user_id ?? 1,
                    'notes' => "Replacement transfer for disputed items in {$transfer->transfer_number}: " . $validated['notes'],
                ]);

                foreach ($transfer->items as $item) {
                    $replacementQty = $item->missing_quantity + $item->damaged_quantity;
                    if ($replacementQty > 0) {
                        StockTransferItem::create([
                            'transfer_id' => $replacement->transfer_id,
                            'product_id' => $item->product_id,
                            'quantity_sent' => $replacementQty,
                            'quantity_received' => 0,
                            'damaged_quantity' => 0,
                            'missing_quantity' => 0,
                        ]);
                    }
                }
            }

            // Move status to Received after resolution
            $transfer->status = 'Received';
            $transfer->notes = ($transfer->notes ? $transfer->notes . "\n" : '') . "Dispute Resolved (" . ucfirst($validated['resolution']) . "): " . $validated['notes'];
            $transfer->save();

            AuditLog::create([
                'user_id' => $user?->user_id,
                'user_name' => $user ? ($user->full_name . ' (' . $user->role . ')') : 'Super Admin',
                'action' => 'Transfer dispute resolved',
                'record' => $transfer->transfer_number,
                'entity_type' => 'StockTransfer',
                'entity_id' => (string) $transfer->transfer_id,
                'old_value' => 'Disputed',
                'new_value' => "Received (Resolved: {$validated['resolution']})",
                'created_at' => now(),
            ]);
        });

        return response()->json($transfer->fresh(['items.product', 'destinationBranch', 'sourceBranch']));
    }

    /**
     * Cancel transfer before dispatch
     * Rule: Dispatched transfers cannot be cancelled. Must be received or resolved through dispute.
     */
    public function cancel(Request $request, $id)
    {
        $transfer = StockTransfer::findOrFail($id);
        $user = $request->user();

        // Dispatched transfers CANNOT be cancelled
        if (in_array($transfer->status, ['Dispatched', 'In transit', 'Partially received', 'Received', 'Disputed'])) {
            return response()->json([
                'message' => 'A dispatched transfer cannot be cancelled. It must be received, or written off through dispute resolution.'
            ], 422);
        }

        if ($transfer->status === 'Cancelled') {
            return response()->json(['message' => 'Transfer is already cancelled.'], 422);
        }

        // Only Super Admin or creator can cancel
        if ($user && $user->role !== 'Super Admin' && $transfer->created_by != $user->user_id) {
            return response()->json(['message' => 'Only Super Admin or the transfer creator can cancel a transfer before dispatch.'], 403);
        }

        $oldStatus = $transfer->status;
        $transfer->status = 'Cancelled';
        $transfer->save();

        AuditLog::create([
            'user_id' => $user?->user_id,
            'user_name' => $user ? ($user->full_name . ' (' . $user->role . ')') : 'Super Admin',
            'action' => 'Transfer cancelled',
            'record' => $transfer->transfer_number,
            'entity_type' => 'StockTransfer',
            'entity_id' => (string) $transfer->transfer_id,
            'old_value' => $oldStatus,
            'new_value' => 'Cancelled',
            'created_at' => now(),
        ]);

        return response()->json($transfer);
    }

    /**
     * Send reminder for unreceived transfer
     */
    public function remind(Request $request, $id)
    {
        $transfer = StockTransfer::with(['destinationBranch', 'sourceBranch'])->findOrFail($id);
        $user = $request->user();

        if (!in_array($transfer->status, ['Dispatched', 'In transit'])) {
            return response()->json(['message' => 'Reminder can only be sent for dispatched or in-transit transfers.'], 422);
        }

        Notification::create([
            'user_id' => null,
            'branch_id' => $transfer->to_branch_id,
            'category' => 'Transfers',
            'title' => 'Transfer not received',
            'message' => "{$transfer->transfer_number} to {$transfer->destinationBranch?->branch_name} unreceived after 24 hours.",
            'link' => '/admin/distribution',
        ]);

        AuditLog::create([
            'user_id' => $user?->user_id,
            'user_name' => $user ? ($user->full_name . ' (' . $user->role . ')') : 'Main Manager',
            'action' => 'Transfer reminder sent',
            'record' => $transfer->transfer_number,
            'entity_type' => 'StockTransfer',
            'entity_id' => (string) $transfer->transfer_id,
            'old_value' => $transfer->status,
            'new_value' => 'Reminder dispatched',
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => "Reminder sent to {$transfer->destinationBranch?->branch_name} and alert logged for Main Branch.",
            'transfer' => $transfer,
        ]);
    }

    /**
     * Submit branch stock request (Super Admin, Main Manager, Branch Admin, Branch Staff)
     */
    public function storeRequest(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,branch_id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.quantity_requested' => 'required|integer|min:1',
        ]);

        if ($user && $user->role !== 'Super Admin' && $user->branch_id && $user->branch_id != $validated['branch_id']) {
            return response()->json(['message' => 'Access denied: You can only submit stock requests for your assigned branch.'], 403);
        }

        $stockReq = DB::transaction(function () use ($validated, $user) {
            $req = StockRequest::create([
                'branch_id' => $validated['branch_id'],
                'status' => 'Pending',
                'requested_by' => $user?->user_id ?? 1,
            ]);

            foreach ($validated['items'] as $item) {
                StockRequestItem::create([
                    'request_id' => $req->request_id,
                    'product_id' => $item['product_id'],
                    'quantity_requested' => $item['quantity_requested'],
                ]);
            }

            Notification::create([
                'user_id' => null,
                'branch_id' => $validated['branch_id'],
                'category' => 'Transfers',
                'title' => 'New stock request',
                'message' => "Branch #{$validated['branch_id']} submitted a stock request for {$req->items->sum('quantity_requested')} units.",
                'link' => '/admin/distribution',
            ]);

            return $req;
        });

        return response()->json($stockReq->load(['items.product', 'branch']), 201);
    }

    /**
     * Convert branch stock request into a transfer (Main Manager or Super Admin)
     */
    public function convertRequest(Request $request, $id)
    {
        $stockReq = StockRequest::with('items')->findOrFail($id);
        $user = $request->user();

        if ($stockReq->status !== 'Pending') {
            return response()->json(['message' => 'Only pending stock requests can be converted.'], 422);
        }

        $mainBranch = Branch::where('branch_type', 'Main')->firstOrFail();
        $transferNumber = $this->generateTransferNumber();

        $threshold = (int) AppSetting::get('approval_threshold_units', 200);
        $totalUnits = $stockReq->items->sum('quantity_requested');
        $initialStatus = ($totalUnits <= $threshold && AppSetting::get('transfer_approval_required', '0') !== '1') ? 'Approved' : 'Draft';

        $transfer = DB::transaction(function () use ($stockReq, $mainBranch, $transferNumber, $initialStatus, $user) {
            $transfer = StockTransfer::create([
                'transfer_number' => $transferNumber,
                'source_branch_id' => $mainBranch->branch_id,
                'destination_branch_id' => $stockReq->branch_id,
                'status' => $initialStatus,
                'created_by' => $user?->user_id ?? 1,
                'approved_by' => ($initialStatus === 'Approved') ? ($user?->user_id ?? 1) : null,
                'notes' => "Converted from branch stock request #{$stockReq->request_id}",
            ]);

            foreach ($stockReq->items as $item) {
                StockTransferItem::create([
                    'transfer_id' => $transfer->transfer_id,
                    'product_id' => $item->product_id,
                    'quantity_sent' => $item->quantity_requested,
                    'quantity_received' => 0,
                    'damaged_quantity' => 0,
                    'missing_quantity' => 0,
                ]);
            }

            $stockReq->update([
                'status' => 'Converted',
                'converted_transfer_id' => $transfer->transfer_id,
            ]);

            AuditLog::create([
                'user_id' => $user?->user_id,
                'user_name' => $user ? ($user->full_name . ' (' . $user->role . ')') : 'Main Manager',
                'action' => 'Stock request converted',
                'record' => $transferNumber,
                'entity_type' => 'StockRequest',
                'entity_id' => (string) $stockReq->request_id,
                'old_value' => 'Pending',
                'new_value' => "Converted to {$transferNumber} ({$initialStatus})",
                'created_at' => now(),
            ]);

            return $transfer;
        });

        return response()->json($transfer->load(['items.product', 'destinationBranch']));
    }
}
