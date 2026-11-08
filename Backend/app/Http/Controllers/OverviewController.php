<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\StockRequest;
use App\Models\Notification;
use App\Models\BranchInventory;
use App\Models\CustomCakeRequest;
use Carbon\Carbon;

class OverviewController extends Controller
{
    /**
     * Dashboard Overview endpoint
     * Serves both Central Dashboard (Super Admin & Main Manager)
     * and Branch Dashboard (locked to single branch for Branch Admin & Staff)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $dateParam = $request->query('date');
        $today = $dateParam ? Carbon::parse($dateParam)->startOfDay() : Carbon::today();
        $startOfWeek = (clone $today)->startOfWeek();

        // Branch-scoped users: Branch Manager, Branch Admin, Branch Staff, Cashier, Delivery Staff, etc.
        $isBranchScoped = ($user && $user->role !== 'Super Admin' && $user->branch_id);

        if ($isBranchScoped) {
            return $this->getBranchOverview($user->branch_id, $today, $startOfWeek);
        }

        // Central Dashboard (Super Admin)
        $filterBranchId = $request->query('branch_id');
        $viewMode = $request->query('view_mode');
        if ($viewMode === 'branch' && $filterBranchId && $filterBranchId !== 'all') {
            return $this->getBranchOverview((int) $filterBranchId, $today, $startOfWeek);
        }

        return $this->getCentralOverview($today, $startOfWeek, $filterBranchId, $dateParam);
    }

    /**
     * Central Dashboard Overview: Whole Business (Super Admin)
     */
    private function getCentralOverview($today, $startOfWeek, $filterBranchId = null, $filterDate = null)
    {
        $hasBranchFilter = ($filterBranchId && $filterBranchId !== 'all');
        $selectedBranchId = $hasBranchFilter ? (int) $filterBranchId : null;

        // 1. Sales & Orders Today
        $ordersTodayQuery = Order::whereDate('placed_at', $today);
        if ($hasBranchFilter) {
            $ordersTodayQuery->where('branch_id', $selectedBranchId);
        }
        $ordersToday = (clone $ordersTodayQuery)->count();
        $revenueToday = (float) (clone $ordersTodayQuery)
            ->where('order_status', '!=', 'Cancelled')
            ->sum('total_amount');

        $pendingOrdersQuery = Order::where('order_status', 'Pending');
        if ($hasBranchFilter) {
            $pendingOrdersQuery->where('branch_id', $selectedBranchId);
        }
        $pendingOrders = $pendingOrdersQuery->count();
        $activeBranches = Branch::where('status', 'Active')->count();

        // 2. Units in Transit
        $inTransitTransfers = StockTransfer::whereIn('status', ['Dispatched', 'In transit']);
        if ($hasBranchFilter) {
            $inTransitTransfers->where('destination_branch_id', $selectedBranchId);
        }
        $transfersList = $inTransitTransfers->get();
        $unitsInTransit = (int) StockTransferItem::whereIn('transfer_id', $transfersList->pluck('transfer_id'))->sum('quantity_sent');

        // 3. Sales by Branch this week (Stacked Bar Chart: Cakes vs Bread & Pastries)
        $branches = Branch::where('status', 'Active')->get();
        $salesByBranch = [];

        foreach ($branches as $branch) {
            $branchWeekOrders = Order::where('branch_id', $branch->branch_id)
                ->where('placed_at', '>=', $startOfWeek)
                ->where('order_status', '!=', 'Cancelled')
                ->pluck('order_id');

            $cakesRevenue = (float) OrderItem::whereIn('order_id', $branchWeekOrders)
                ->whereHas('product.category', function ($q) {
                    $q->where('slug', 'cakes')->orWhere('category_name', 'like', '%Cake%');
                })
                ->sum('subtotal');

            $breadRevenue = (float) OrderItem::whereIn('order_id', $branchWeekOrders)
                ->whereHas('product.category', function ($q) {
                    $q->where('slug', '!=', 'cakes')->where('category_name', 'not like', '%Cake%');
                })
                ->sum('subtotal');

            $totalBranchSales = $cakesRevenue + $breadRevenue;

            $salesByBranch[] = [
                'branch_id' => $branch->branch_id,
                'branch' => $branch->branch_type === 'Main' ? 'Main' : $branch->branch_name,
                'cakes' => $cakesRevenue,
                'bread' => $breadRevenue,
                'total' => $totalBranchSales,
            ];
        }

        // 4. Real Dynamic Needs Attention Feed from Database
        $needsAttention = [];

        // A. Disputed Transfers
        $disputedTransfers = StockTransfer::with(['items', 'destinationBranch'])
            ->where('status', 'Disputed')
            ->latest('updated_at')
            ->take(2)
            ->get();
        foreach ($disputedTransfers as $dt) {
            $sent = $dt->items->sum('quantity_sent');
            $rcv = $dt->items->sum('quantity_received');
            $needsAttention[] = [
                'type' => 'disputed',
                'text' => "Transfer disputed. {$dt->transfer_number}: {$sent} sent to {$dt->destinationBranch?->branch_name}, {$rcv} received. Reason needed to resolve.",
            ];
        }

        // B. In transit / Overdue Transfers
        $inTransitLatest = StockTransfer::with(['destinationBranch'])
            ->whereIn('status', ['Dispatched', 'In transit'])
            ->latest('dispatched_at')
            ->first();
        if ($inTransitLatest) {
            $needsAttention[] = [
                'type' => 'in_transit',
                'text' => "In transit. {$inTransitLatest->transfer_number} to {$inTransitLatest->destinationBranch?->branch_name} awaiting branch confirmation.",
            ];
        }

        // C. Low Stock Products
        $lowStockInv = BranchInventory::with(['branch', 'product'])
            ->whereRaw('(quantity_on_hand - reserved_quantity) <= reorder_level')
            ->whereHas('branch', fn($q) => $q->where('status', 'Active'))
            ->first();
        if ($lowStockInv) {
            $avail = max(0, $lowStockInv->quantity_on_hand - $lowStockInv->reserved_quantity);
            $needsAttention[] = [
                'type' => 'low_stock',
                'text' => "Low stock. {$lowStockInv->branch?->branch_name} has {$avail} {$lowStockInv->product?->product_name} left (reorder level {$lowStockInv->reorder_level}).",
            ];
        }

        // D. Pending Branch Stock Requests
        $pendingReq = StockRequest::with(['branch', 'items.product'])
            ->where('status', 'Pending')
            ->latest('created_at')
            ->first();
        if ($pendingReq) {
            $item = $pendingReq->items->first();
            $needsAttention[] = [
                'type' => 'request',
                'text' => "New request. {$pendingReq->branch?->branch_name} asked for {$item?->quantity_requested} {$item?->product?->product_name}s.",
            ];
        }

        // 5. Branches Today Status Table
        $branchesToday = [];
        foreach ($branches as $b) {
            $bOrders = Order::where('branch_id', $b->branch_id)->whereDate('placed_at', $today)->count();
            $bRev = (float) Order::where('branch_id', $b->branch_id)
                ->whereDate('placed_at', $today)
                ->where('order_status', '!=', 'Cancelled')
                ->sum('total_amount');

            // Stock Health
            $lowCount = BranchInventory::where('branch_id', $b->branch_id)
                ->whereRaw('(quantity_on_hand - reserved_quantity) <= reorder_level')
                ->count();
            $soldOutCount = BranchInventory::where('branch_id', $b->branch_id)
                ->whereRaw('(quantity_on_hand - reserved_quantity) <= 0')
                ->count();

            $health = 'Healthy';
            if ($soldOutCount > 0) {
                $health = "{$soldOutCount} sold out";
            } elseif ($lowCount > 0) {
                $health = "{$lowCount} items low";
            }

            // Open Transfers
            $openTransfers = StockTransfer::where('destination_branch_id', $b->branch_id)
                ->whereIn('status', ['Dispatched', 'In transit', 'Disputed', 'Approved'])
                ->latest('updated_at')
                ->first();

            $transferStatus = 'None';
            if ($openTransfers) {
                $transferStatus = "1 " . strtolower($openTransfers->status);
            }

            // Custom cakes active for this branch
            $activeCakesCount = CustomCakeRequest::where('branch_id', $b->branch_id)
                ->whereIn('status', ['Submitted', 'Quoted', 'Confirmed', 'Preparing', 'Ready'])
                ->count();

            // Active staff count for this branch
            $staffCount = \App\Models\User::where('branch_id', $b->branch_id)
                ->where('role', '!=', 'Customer')
                ->count();

            $manager = \App\Models\User::where('branch_id', $b->branch_id)
                ->whereIn('role', ['Branch Manager', 'Branch Admin', 'Main Branch Manager'])
                ->first();

            $branchesToday[] = [
                'branch_id' => $b->branch_id,
                'branch' => "{$b->branch_name}" . ($b->branch_type === 'Regular' ? " ({$b->branch_code})" : ""),
                'branch_name' => $b->branch_name,
                'branch_code' => $b->branch_code,
                'manager_name' => $manager?->full_name ?? ($b->manager?->full_name ?? 'Unassigned'),
                'staff_count' => $staffCount,
                'orders' => $bOrders,
                'revenue' => $bRev,
                'active_cakes' => $activeCakesCount,
                'stock_health' => $health,
                'open_transfers' => $transferStatus,
                'is_shopping_enabled' => $b->is_shopping_enabled,
            ];
        }

        // 6. Branch Manager Submitted Reports (Section 3 Requirement)
        $reportsQuery = \App\Models\StaffReport::with(['user', 'branch'])->latest('created_at');
        if ($hasBranchFilter) {
            $reportsQuery->where('branch_id', $selectedBranchId);
        }
        if ($filterDate) {
            $reportsQuery->whereDate('created_at', $filterDate);
        }
        $submittedReports = $reportsQuery->take(10)->get()
            ->map(function ($r) {
                return [
                    'report_id' => $r->report_id,
                    'title' => $r->title,
                    'report_type' => $r->report_type,
                    'content' => $r->content,
                    'status' => $r->status,
                    'branch_name' => $r->branch?->branch_name ?? 'Branch',
                    'author_name' => $r->user?->full_name ?? 'Staff',
                    'author_role' => $r->user?->role ?? '',
                    'date' => $r->created_at ? $r->created_at->format('M d, H:i') : '—',
                ];
            });

        // 7. Custom Cake Orders Summary (Section 3 Requirement)
        $cakeBaseQuery = CustomCakeRequest::query();
        if ($hasBranchFilter) {
            $cakeBaseQuery->where('branch_id', $selectedBranchId);
        }
        if ($filterDate) {
            $cakeBaseQuery->whereDate('created_at', $filterDate);
        }
        $customCakesSummary = [
            'submitted' => (clone $cakeBaseQuery)->where('status', 'Submitted')->count(),
            'quoted' => (clone $cakeBaseQuery)->where('status', 'Quoted')->count(),
            'confirmed' => (clone $cakeBaseQuery)->where('status', 'Confirmed')->count(),
            'ready' => (clone $cakeBaseQuery)->where('status', 'Ready')->count(),
            'delivered' => (clone $cakeBaseQuery)->whereIn('status', ['Delivered', 'Picked up'])->count(),
            'total_active' => (clone $cakeBaseQuery)->whereNotIn('status', ['Delivered', 'Picked up', 'Cancelled', 'Rejected'])->count(),
        ];

        // 8. Recent Product & Price changes recorded in Audit Logs (Section 3 Requirement)
        $auditQuery = \App\Models\AuditLog::where('action', 'Price change')->latest('created_at');
        if ($filterDate) {
            $auditQuery->whereDate('created_at', $filterDate);
        }
        $priceChanges = $auditQuery->take(8)->get()
            ->map(function ($log) {
                return [
                    'log_id' => $log->log_id,
                    'author' => $log->user_name,
                    'record' => $log->record,
                    'old_value' => $log->old_value,
                    'new_value' => $log->new_value,
                    'time' => $log->created_at ? $log->created_at->format('M d, H:i') : '—',
                ];
            });

        return response()->json([
            'is_branch_view' => false,
            'revenue_today' => $revenueToday,
            'orders_today' => $ordersToday,
            'pending_orders' => $pendingOrders,
            'active_branches' => $activeBranches,
            'units_in_transit' => $unitsInTransit,
            'sales_by_branch' => $salesByBranch,
            'needs_attention' => $needsAttention,
            'branches_today' => $branchesToday,
            'submitted_reports' => $submittedReports,
            'custom_cakes_summary' => $customCakesSummary,
            'recent_price_changes' => $priceChanges,
        ]);
    }

    /**
     * Branch Dashboard Overview: Locked to Single Assigned Branch (Branch Admin & Staff)
     */
    private function getBranchOverview($branchId, $today, $startOfWeek)
    {
        $branch = Branch::findOrFail($branchId);

        // 1. Branch Sales & Orders Today
        $ordersTodayQuery = Order::where('branch_id', $branchId)->whereDate('placed_at', $today);
        $ordersToday = (clone $ordersTodayQuery)->count();
        $revenueToday = (float) (clone $ordersTodayQuery)
            ->where('order_status', '!=', 'Cancelled')
            ->sum('total_amount');

        $pendingOrders = Order::where('branch_id', $branchId)->where('order_status', 'Pending')->count();
        $confirmedOrders = Order::where('branch_id', $branchId)->where('order_status', 'Confirmed')->count();
        $preparingOrders = Order::where('branch_id', $branchId)->where('order_status', 'Preparing')->count();
        $readyOrders = Order::where('branch_id', $branchId)->where('order_status', 'Ready')->count();

        // 2. Stock Summary for this Branch
        $inventoryRecords = BranchInventory::with('product')
            ->where('branch_id', $branchId)
            ->get();

        $totalOnHand = (int) $inventoryRecords->sum('quantity_on_hand');
        $totalReserved = (int) $inventoryRecords->sum('reserved_quantity');
        $totalAvailable = max(0, $totalOnHand - $totalReserved);

        $lowStockItems = $inventoryRecords->filter(function ($inv) {
            $avail = max(0, $inv->quantity_on_hand - $inv->reserved_quantity);
            return $avail > 0 && $avail <= $inv->reorder_level;
        });

        $soldOutItems = $inventoryRecords->filter(function ($inv) {
            return ($inv->quantity_on_hand - $inv->reserved_quantity) <= 0;
        });

        // 3. Incoming Transfers for this Branch
        $incomingTransfers = StockTransfer::with('items')
            ->where('destination_branch_id', $branchId)
            ->whereIn('status', ['Dispatched', 'In transit'])
            ->get();
        $incomingUnits = (int) StockTransferItem::whereIn('transfer_id', $incomingTransfers->pluck('transfer_id'))->sum('quantity_sent');

        // 4. Branch Specific Needs Attention
        $needsAttention = [];

        foreach ($incomingTransfers as $it) {
            $needsAttention[] = [
                'type' => 'in_transit',
                'text' => "Incoming transfer {$it->transfer_number}: {$it->items->sum('quantity_sent')} units in transit from Main Branch. Ready for count & receipt confirmation.",
            ];
        }

        $disputedForBranch = StockTransfer::where('destination_branch_id', $branchId)
            ->where('status', 'Disputed')
            ->first();
        if ($disputedForBranch) {
            $needsAttention[] = [
                'type' => 'disputed',
                'text' => "Transfer {$disputedForBranch->transfer_number} disputed with Main Branch. Super Admin / Main Manager will resolve discrepancy.",
            ];
        }

        foreach ($lowStockItems->take(2) as $low) {
            $avail = max(0, $low->quantity_on_hand - $low->reserved_quantity);
            $needsAttention[] = [
                'type' => 'low_stock',
                'text' => "Low stock: {$low->product?->product_name} has {$avail} units left (reorder level {$low->reorder_level}). Request stock from Main Branch.",
            ];
        }

        $pendingCustomCakes = CustomCakeRequest::where('branch_id', $branchId)->where('status', 'Submitted')->count();
        if ($pendingCustomCakes > 0) {
            $needsAttention[] = [
                'type' => 'request',
                'text' => "{$pendingCustomCakes} custom cake request(s) awaiting quote review.",
            ];
        }

        // 5. Recent Orders at this branch
        $recentOrders = Order::with(['user', 'items'])
            ->where('branch_id', $branchId)
            ->latest('placed_at')
            ->take(5)
            ->get()
            ->map(function ($o) {
                return [
                    'order_id' => $o->order_id,
                    'order_number' => $o->order_number,
                    'customer' => $o->user?->full_name ?? 'Customer',
                    'method' => $o->fulfillment_method,
                    'total' => (float) $o->total_amount,
                    'status' => $o->order_status,
                    'placed' => $o->placed_at ? $o->placed_at->format('H:i') : '—',
                ];
            });

        // 6. Stock Table (sample items with status for branch overview)
        $stockItemsList = $inventoryRecords->map(function ($inv) {
            $avail = max(0, $inv->quantity_on_hand - $inv->reserved_quantity);
            $status = 'Healthy';
            if ($avail <= 0) $status = 'Sold out';
            elseif ($avail <= $inv->reorder_level) $status = 'Low';

            return [
                'product_id' => $inv->product_id,
                'product_name' => $inv->product?->product_name ?? "Product #{$inv->product_id}",
                'on_hand' => (int) $inv->quantity_on_hand,
                'reserved' => (int) $inv->reserved_quantity,
                'available' => $avail,
                'reorder_level' => (int) $inv->reorder_level,
                'status' => $status,
            ];
        });

        return response()->json([
            'is_branch_view' => true,
            'branch' => [
                'branch_id' => $branch->branch_id,
                'branch_name' => $branch->branch_name,
                'branch_code' => $branch->branch_code,
                'phone' => $branch->phone,
                'address' => $branch->address,
            ],
            'revenue_today' => $revenueToday,
            'orders_today' => $ordersToday,
            'pending_orders' => $pendingOrders,
            'confirmed_orders' => $confirmedOrders,
            'preparing_orders' => $preparingOrders,
            'ready_orders' => $readyOrders,
            'stock_summary' => [
                'units_on_hand' => $totalOnHand,
                'reserved' => $totalReserved,
                'available' => $totalAvailable,
                'low_stock_items' => $lowStockItems->count(),
                'sold_out' => $soldOutItems->count(),
            ],
            'incoming_transfers' => $incomingTransfers->count(),
            'incoming_units' => $incomingUnits,
            'needs_attention' => $needsAttention,
            'recent_orders' => $recentOrders,
            'stock_items' => $stockItemsList,
        ]);
    }
}
