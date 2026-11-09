<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\BranchConsolidatedReport;
use App\Models\CustomCakeRequest;
use App\Models\Order;
use App\Models\SellerDailyReport;
use App\Models\SellerReportCustomCake;
use App\Models\SellerReportOrder;
use App\Models\User;
use Carbon\Carbon;

class OrderReportController extends Controller
{
    /**
     * Helper to verify seller/staff access
     */
    protected function checkSellerAccess(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        // Sellers, Cashiers, Branch Staff, and Branch Managers can create/record reports
        if (!$user->branch_id && $user->role !== 'Super Admin') {
            abort(403, 'You must be assigned to a branch to manage daily sales reports.');
        }

        return $user;
    }

    /**
     * Helper to verify manager access
     */
    protected function checkManagerAccess(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $managerRoles = ['Branch Manager', 'Main Branch Manager', 'Branch Admin', 'Super Admin'];
        if (!in_array($user->role, $managerRoles)) {
            abort(403, 'Only Branch Managers and Admins can verify and consolidate reports.');
        }

        return $user;
    }

    /**
     * Helper to verify super admin access
     */
    protected function checkSuperAdminAccess(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'Super Admin') {
            abort(403, 'Super Admin access required.');
        }

        return $user;
    }

    // =========================================================================
    // LEVEL 1: BRANCH SELLER ENDPOINTS
    // =========================================================================

    /**
     * Pre-populate helper: pulls today's branch orders & custom cakes
     * to eliminate duplicate entry and allow 1-click import into seller report
     */
    public function sellerTodayOrders(Request $request)
    {
        $user = $this->checkSellerAccess($request);
        $branchId = $user->branch_id ?? $request->query('branch_id');
        $date = $request->query('date', Carbon::today()->toDateString());

        if (!$branchId) {
            return response()->json([
                'success' => false,
                'message' => 'No branch assigned to this user.'
            ], 422);
        }

        $branch = Branch::find($branchId);

        // Fetch regular orders placed for this branch on the given date
        $orders = Order::with('items')
            ->where('branch_id', $branchId)
            ->whereDate('placed_at', $date)
            ->where('order_status', '!=', 'Cancelled')
            ->get();

        $regularRows = [];
        foreach ($orders as $order) {
            if ($order->items && $order->items->count() > 0) {
                foreach ($order->items as $item) {
                    $qty = (int) $item->quantity;
                    $price = (float) $item->unit_price;
                    $regularRows[] = [
                        'order_id' => $order->order_id,
                        'order_number' => $order->order_number,
                        'product_name' => $item->product_name ?? 'Bakery Item',
                        'category' => 'Pastry',
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'total_amount' => round($qty * $price, 2),
                        'order_status' => $order->order_status ?? 'Completed',
                        'payment_status' => 'Paid',
                        'customer_info' => $order->customer_name ? ($order->customer_name . ' (' . ($order->customer_phone ?? 'N/A') . ')') : 'In-store Customer',
                    ];
                }
            } else {
                $regularRows[] = [
                    'order_id' => $order->order_id,
                    'order_number' => $order->order_number,
                    'product_name' => 'General Order',
                    'category' => 'General',
                    'quantity' => 1,
                    'unit_price' => (float) $order->total_amount,
                    'total_amount' => (float) $order->total_amount,
                    'order_status' => $order->order_status ?? 'Completed',
                    'payment_status' => 'Paid',
                    'customer_info' => $order->customer_name ? ($order->customer_name . ' (' . ($order->customer_phone ?? 'N/A') . ')') : 'In-store Customer',
                ];
            }
        }

        // Fetch custom cakes for this branch
        $cakes = CustomCakeRequest::where('branch_id', $branchId)
            ->where(function ($q) use ($date) {
                $q->whereDate('created_at', $date)
                  ->orWhereDate('needed_by', $date);
            })
            ->where('status', '!=', 'Rejected')
            ->get();

        $customCakeRows = [];
        foreach ($cakes as $cake) {
            $unitPrice = (float) ($cake->quoted_price ?? 0);
            $advance = (float) ($cake->deposit_paid ?? 0);
            $qty = 1;
            $totalPrice = round($qty * $unitPrice, 2);
            $remaining = max(0, round($totalPrice - $advance, 2));

            $customCakeRows[] = [
                'custom_cake_id' => $cake->request_id,
                'cake_order_number' => $cake->request_number ?? ('CCR-' . $cake->request_id),
                'customer_name' => $cake->customer_name ?? 'Customer',
                'cake_category' => 'Custom Celebration',
                'cake_type_design' => ($cake->flavor ?? 'Vanilla') . ' - ' . ($cake->design ?? 'Bespoke'),
                'size_weight' => $cake->cake_size ?? '2 kg',
                'quantity' => $qty,
                'customization_details' => $cake->message ?? 'No specific message',
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'advance_payment' => $advance,
                'remaining_balance' => $remaining,
                'delivery_date' => $cake->needed_by ? Carbon::parse($cake->needed_by)->toDateString() : $date,
                'order_status' => in_array($cake->status, ['Approved', 'Completed', 'In Production']) ? 'Confirmed' : 'Pending',
                'payment_status' => $advance >= $totalPrice && $totalPrice > 0 ? 'Paid' : ($advance > 0 ? 'Partially Paid' : 'Unpaid'),
            ];
        }

        // Suggested Reference Number: DSR-BR{id}-YYYYMMDD-{RAND}
        $ref = 'DSR-BR' . $branchId . '-' . Carbon::parse($date)->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        return response()->json([
            'success' => true,
            'branch' => $branch,
            'seller' => [
                'user_id' => $user->user_id,
                'name' => $user->full_name ?? $user->name,
                'role' => $user->role,
            ],
            'report_date' => $date,
            'suggested_reference' => $ref,
            'regular_orders' => $regularRows,
            'custom_cakes' => $customCakeRows,
        ]);
    }

    /**
     * List seller daily reports scoped by role
     */
    public function sellerReportsIndex(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $query = SellerDailyReport::with(['branch', 'seller:user_id,full_name,email', 'manager:user_id,full_name'])
            ->orderBy('report_date', 'desc')
            ->orderBy('id', 'desc');

        // Scoping
        if ($user->role === 'Super Admin') {
            if ($request->query('branch_id')) {
                $query->where('branch_id', $request->query('branch_id'));
            }
        } elseif (in_array($user->role, ['Branch Manager', 'Main Branch Manager', 'Branch Admin'])) {
            $query->where('branch_id', $user->branch_id);
        } else {
            // Seller/Cashier sees only their own reports
            $query->where('seller_id', $user->user_id);
        }

        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->query('from_date')) {
            $query->whereDate('report_date', '>=', $request->query('from_date'));
        }
        if ($request->query('to_date')) {
            $query->whereDate('report_date', '<=', $request->query('to_date'));
        }

        $reports = $query->paginate($request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }

    /**
     * Show single seller daily report with lines
     */
    public function sellerReportShow(Request $request, $id)
    {
        $user = $request->user();
        $report = SellerDailyReport::with([
            'branch',
            'seller:user_id,full_name,email',
            'manager:user_id,full_name',
            'orders',
            'customCakes',
            'consolidatedReport',
        ])->findOrFail($id);

        // Security check
        if ($user->role !== 'Super Admin') {
            if (in_array($user->role, ['Branch Manager', 'Main Branch Manager', 'Branch Admin'])) {
                if ((int) $report->branch_id !== (int) $user->branch_id) {
                    abort(403, 'Access denied: You can only view reports for your assigned branch.');
                }
            } else {
                if ((int) $report->seller_id !== (int) $user->user_id) {
                    abort(403, 'Access denied: You can only view your own reports.');
                }
            }
        }

        return response()->json([
            'success' => true,
            'report' => $report,
        ]);
    }

    /**
     * Store a new seller daily report (Draft or Submit)
     */
    public function sellerReportStore(Request $request)
    {
        $user = $this->checkSellerAccess($request);

        $branchId = $user->branch_id ?? $request->input('branch_id');
        if (!$branchId) {
            return response()->json(['success' => false, 'message' => 'Branch ID is required.'], 422);
        }

        $validated = $request->validate([
            'report_date' => 'required|date',
            'report_reference' => 'nullable|string',
            'action' => 'required|in:draft,submit',
            'orders' => 'present|array',
            'orders.*.order_number' => 'required|string',
            'orders.*.product_name' => 'required|string',
            'orders.*.category' => 'nullable|string',
            'orders.*.quantity' => 'required|numeric|min:1',
            'orders.*.unit_price' => 'required|numeric|min:0',
            'orders.*.order_status' => 'nullable|string',
            'orders.*.payment_status' => 'nullable|string',
            'orders.*.customer_info' => 'nullable|string',
            'custom_cakes' => 'present|array',
            'custom_cakes.*.cake_order_number' => 'required|string',
            'custom_cakes.*.customer_name' => 'required|string',
            'custom_cakes.*.cake_category' => 'nullable|string',
            'custom_cakes.*.cake_type_design' => 'nullable|string',
            'custom_cakes.*.size_weight' => 'nullable|string',
            'custom_cakes.*.quantity' => 'required|numeric|min:1',
            'custom_cakes.*.customization_details' => 'nullable|string',
            'custom_cakes.*.unit_price' => 'required|numeric|min:0',
            'custom_cakes.*.advance_payment' => 'nullable|numeric|min:0',
            'custom_cakes.*.delivery_date' => 'nullable|date',
            'custom_cakes.*.order_status' => 'nullable|string',
            'custom_cakes.*.payment_status' => 'nullable|string',
        ]);

        $reportDate = Carbon::parse($validated['report_date'])->toDateString();
        $ref = $validated['report_reference'] ?: ('DSR-BR' . $branchId . '-' . Carbon::parse($reportDate)->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4)));

        // Status determination
        $isSubmit = $validated['action'] === 'submit';
        $status = $isSubmit ? 'Pending Verification' : 'Draft';

        DB::beginTransaction();
        try {
            $report = SellerDailyReport::create([
                'report_reference' => $ref,
                'branch_id' => $branchId,
                'seller_id' => $user->user_id,
                'report_date' => $reportDate,
                'status' => $status,
                'submitted_at' => $isSubmit ? now() : null,
            ]);

            // Insert Regular Orders
            foreach ($validated['orders'] as $row) {
                $qty = (int) $row['quantity'];
                $price = (float) $row['unit_price'];
                $total = round($qty * $price, 2);

                SellerReportOrder::create([
                    'report_id' => $report->id,
                    'order_id' => $row['order_id'] ?? null,
                    'order_number' => $row['order_number'],
                    'product_name' => $row['product_name'],
                    'category' => $row['category'] ?? 'Pastry',
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_amount' => $total,
                    'order_status' => $row['order_status'] ?? 'Completed',
                    'payment_status' => $row['payment_status'] ?? 'Paid',
                    'customer_info' => $row['customer_info'] ?? null,
                ]);
            }

            // Insert Custom Cakes
            foreach ($validated['custom_cakes'] as $row) {
                $qty = (int) $row['quantity'];
                $price = (float) $row['unit_price'];
                $total = round($qty * $price, 2);
                $advance = (float) ($row['advance_payment'] ?? 0);
                $remaining = max(0, round($total - $advance, 2));

                SellerReportCustomCake::create([
                    'report_id' => $report->id,
                    'custom_cake_id' => $row['custom_cake_id'] ?? null,
                    'cake_order_number' => $row['cake_order_number'],
                    'customer_name' => $row['customer_name'],
                    'cake_category' => $row['cake_category'] ?? 'Celebration Cake',
                    'cake_type_design' => $row['cake_type_design'] ?? 'Custom',
                    'size_weight' => $row['size_weight'] ?? '2 kg',
                    'quantity' => $qty,
                    'customization_details' => $row['customization_details'] ?? null,
                    'unit_price' => $price,
                    'total_price' => $total,
                    'advance_payment' => $advance,
                    'remaining_balance' => $remaining,
                    'delivery_date' => !empty($row['delivery_date']) ? Carbon::parse($row['delivery_date'])->toDateString() : null,
                    'order_status' => $row['order_status'] ?? 'Confirmed',
                    'payment_status' => $row['payment_status'] ?? ($advance >= $total ? 'Paid' : ($advance > 0 ? 'Partially Paid' : 'Unpaid')),
                ]);
            }

            // Recalculate summary totals accurately on backend
            $report->recalculateTotals();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isSubmit ? 'Daily sales report submitted to Branch Manager successfully.' : 'Daily sales report saved as draft.',
                'report' => $report->load(['orders', 'customCakes', 'branch']),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save daily report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update existing seller report (only if Draft or Returned for Correction)
     */
    public function sellerReportUpdate(Request $request, $id)
    {
        $user = $this->checkSellerAccess($request);
        $report = SellerDailyReport::findOrFail($id);

        if ((int) $report->seller_id !== (int) $user->user_id && $user->role !== 'Super Admin') {
            abort(403, 'You can only update your own reports.');
        }

        if (!in_array($report->status, ['Draft', 'Returned for Correction'])) {
            return response()->json([
                'success' => false,
                'message' => 'This report cannot be edited because it is ' . $report->status . '.',
            ], 422);
        }

        $validated = $request->validate([
            'report_date' => 'sometimes|date',
            'action' => 'required|in:draft,submit',
            'orders' => 'present|array',
            'custom_cakes' => 'present|array',
        ]);

        $isSubmit = $validated['action'] === 'submit';

        DB::beginTransaction();
        try {
            if (!empty($validated['report_date'])) {
                $report->report_date = Carbon::parse($validated['report_date'])->toDateString();
            }

            $report->status = $isSubmit ? 'Pending Verification' : 'Draft';
            if ($isSubmit) {
                $report->submitted_at = now();
            }

            // Replace line items
            $report->orders()->delete();
            foreach ($validated['orders'] as $row) {
                $qty = (int) ($row['quantity'] ?? 1);
                $price = (float) ($row['unit_price'] ?? 0);
                SellerReportOrder::create([
                    'report_id' => $report->id,
                    'order_id' => $row['order_id'] ?? null,
                    'order_number' => $row['order_number'] ?? 'ORD',
                    'product_name' => $row['product_name'] ?? 'Item',
                    'category' => $row['category'] ?? 'Pastry',
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_amount' => round($qty * $price, 2),
                    'order_status' => $row['order_status'] ?? 'Completed',
                    'payment_status' => $row['payment_status'] ?? 'Paid',
                    'customer_info' => $row['customer_info'] ?? null,
                ]);
            }

            $report->customCakes()->delete();
            foreach ($validated['custom_cakes'] as $row) {
                $qty = (int) ($row['quantity'] ?? 1);
                $price = (float) ($row['unit_price'] ?? 0);
                $total = round($qty * $price, 2);
                $advance = (float) ($row['advance_payment'] ?? 0);
                $remaining = max(0, round($total - $advance, 2));

                SellerReportCustomCake::create([
                    'report_id' => $report->id,
                    'custom_cake_id' => $row['custom_cake_id'] ?? null,
                    'cake_order_number' => $row['cake_order_number'] ?? 'CCR',
                    'customer_name' => $row['customer_name'] ?? 'Customer',
                    'cake_category' => $row['cake_category'] ?? 'Celebration Cake',
                    'cake_type_design' => $row['cake_type_design'] ?? 'Custom',
                    'size_weight' => $row['size_weight'] ?? '2 kg',
                    'quantity' => $qty,
                    'customization_details' => $row['customization_details'] ?? null,
                    'unit_price' => $price,
                    'total_price' => $total,
                    'advance_payment' => $advance,
                    'remaining_balance' => $remaining,
                    'delivery_date' => !empty($row['delivery_date']) ? Carbon::parse($row['delivery_date'])->toDateString() : null,
                    'order_status' => $row['order_status'] ?? 'Confirmed',
                    'payment_status' => $row['payment_status'] ?? 'Partially Paid',
                ]);
            }

            $report->recalculateTotals();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isSubmit ? 'Report re-submitted to Branch Manager.' : 'Draft updated successfully.',
                'report' => $report->fresh(['orders', 'customCakes', 'branch']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update report: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // LEVEL 2: BRANCH MANAGER ENDPOINTS (VERIFICATION & CONSOLIDATION)
    // =========================================================================

    /**
     * Branch Manager reviews a seller report: Approve or Return for Correction
     */
    public function managerVerifyReport(Request $request, $id)
    {
        $manager = $this->checkManagerAccess($request);
        $report = SellerDailyReport::with(['orders', 'customCakes', 'branch'])->findOrFail($id);

        // Security check
        if ($manager->role !== 'Super Admin' && (int) $report->branch_id !== (int) $manager->branch_id) {
            abort(403, 'Access denied: You can only verify reports for your assigned branch.');
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,return',
            'manager_notes' => 'nullable|string|max:1000',
        ]);

        if ($validated['action'] === 'return') {
            if (empty($validated['manager_notes'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide a correction note explaining what needs to be fixed.',
                ], 422);
            }

            $report->status = 'Returned for Correction';
            $report->manager_id = $manager->user_id;
            $report->manager_notes = $validated['manager_notes'];
            $report->returned_at = now();
            $report->save();

            return response()->json([
                'success' => true,
                'message' => 'Report has been returned to seller for correction with notes.',
                'report' => $report,
            ]);
        }

        // Action == 'approve'
        $report->status = 'Approved';
        $report->manager_id = $manager->user_id;
        $report->manager_notes = $validated['manager_notes'] ?? null;
        $report->verified_at = now();
        $report->save();

        return response()->json([
            'success' => true,
            'message' => 'Seller daily report verified and approved successfully.',
            'report' => $report,
        ]);
    }

    /**
     * Get approved seller reports ready for consolidation for manager's branch
     */
    public function managerPendingConsolidation(Request $request)
    {
        $manager = $this->checkManagerAccess($request);
        $branchId = $manager->branch_id ?? $request->query('branch_id');

        if (!$branchId) {
            return response()->json(['success' => false, 'message' => 'Branch ID required.'], 422);
        }

        $fromDate = $request->query('from_date', Carbon::today()->startOfWeek()->toDateString());
        $toDate = $request->query('to_date', Carbon::today()->toDateString());

        // Approved reports for this branch not yet assigned to a consolidated branch report
        $sellerReports = SellerDailyReport::with(['seller:user_id,full_name,email', 'orders', 'customCakes'])
            ->where('branch_id', $branchId)
            ->where('status', 'Approved')
            ->whereNull('branch_report_id')
            ->whereBetween('report_date', [$fromDate, $toDate])
            ->get();

        $totalRegularOrders = (int) $sellerReports->sum('regular_orders_count');
        $totalRegularRevenue = (float) $sellerReports->sum('regular_order_revenue');
        $totalCustomCakeOrders = (int) $sellerReports->sum('custom_cake_orders_count');
        $totalCustomCakeRevenue = (float) $sellerReports->sum('custom_cake_revenue');
        $totalAdvance = (float) $sellerReports->sum('total_advance_payments');
        $totalOutstanding = (float) $sellerReports->sum('total_outstanding_balances');
        $totalBranchRevenue = (float) $sellerReports->sum('combined_daily_revenue');
        $totalPaymentsReceived = (float) $sellerReports->sum('total_payments_received');

        $suggestedRef = 'BCR-BR' . $branchId . '-' . Carbon::now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        return response()->json([
            'success' => true,
            'branch_id' => (int) $branchId,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'suggested_reference' => $suggestedRef,
            'seller_reports_count' => $sellerReports->count(),
            'sellers_count' => $sellerReports->pluck('seller_id')->unique()->count(),
            'summary' => [
                'total_regular_orders' => $totalRegularOrders,
                'total_regular_revenue' => $totalRegularRevenue,
                'total_custom_cake_orders' => $totalCustomCakeOrders,
                'total_custom_cake_revenue' => $totalCustomCakeRevenue,
                'total_advance_payments' => $totalAdvance,
                'total_outstanding_balances' => $totalOutstanding,
                'total_branch_revenue' => $totalBranchRevenue,
                'total_payments_received' => $totalPaymentsReceived,
            ],
            'reports' => $sellerReports,
        ]);
    }

    /**
     * Submit Verified Branch Sales Report to Super Admin
     */
    public function managerConsolidateAndSubmit(Request $request)
    {
        $manager = $this->checkManagerAccess($request);
        $branchId = $manager->branch_id ?? $request->input('branch_id');

        if (!$branchId) {
            return response()->json(['success' => false, 'message' => 'Branch ID required.'], 422);
        }

        $validated = $request->validate([
            'period_type' => 'required|in:Daily,Weekly,Monthly,Custom',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'confirmed_accuracy' => 'required|accepted',
            'manager_notes' => 'nullable|string|max:2000',
            'report_reference' => 'nullable|string',
        ]);

        $start = Carbon::parse($validated['period_start'])->toDateString();
        $end = Carbon::parse($validated['period_end'])->toDateString();

        // Fetch eligible approved seller reports
        $sellerReports = SellerDailyReport::with(['orders', 'customCakes', 'seller'])
            ->where('branch_id', $branchId)
            ->where('status', 'Approved')
            ->whereNull('branch_report_id')
            ->whereBetween('report_date', [$start, $end])
            ->get();

        if ($sellerReports->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No approved seller reports found for this branch in the selected date range to consolidate.',
            ], 422);
        }

        $ref = $validated['report_reference'] ?: ('BCR-BR' . $branchId . '-' . Carbon::now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4)));

        DB::beginTransaction();
        try {
            $consolidated = new BranchConsolidatedReport();
            $consolidated->report_reference = $ref;
            $consolidated->branch_id = $branchId;
            $consolidated->manager_id = $manager->user_id;
            $consolidated->period_type = $validated['period_type'];
            $consolidated->period_start = $start;
            $consolidated->period_end = $end;
            $consolidated->status = 'Submitted to Super Admin';
            $consolidated->confirmed_accuracy = true;
            $consolidated->manager_notes = $validated['manager_notes'] ?? null;
            $consolidated->submitted_at = now();
            $consolidated->is_locked = true;

            // Consolidate all metrics, counts, and breakdown lists
            $consolidated->consolidateFromSellerReports($sellerReports);
            $consolidated->save();

            // Link seller reports to this consolidated report
            SellerDailyReport::whereIn('id', $sellerReports->pluck('id'))->update([
                'branch_report_id' => $consolidated->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Verified Branch Sales Report submitted to Super Admin successfully.',
                'consolidated_report' => $consolidated->load(['branch', 'manager']),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to consolidate report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List branch consolidated reports
     */
    public function managerConsolidatedReports(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $query = BranchConsolidatedReport::with(['branch', 'manager:user_id,full_name,email', 'superAdmin:user_id,full_name'])
            ->orderBy('period_end', 'desc')
            ->orderBy('id', 'desc');

        if ($user->role !== 'Super Admin') {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->query('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }

        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
        }

        $reports = $query->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }

    // =========================================================================
    // LEVEL 3: SUPER ADMIN ENDPOINTS (CENTRALIZED MONITORING & APPROVAL)
    // =========================================================================

    /**
     * Super Admin Multi-Branch Sales & Order Reports Overview
     */
    public function superAdminOverview(Request $request)
    {
        $this->checkSuperAdminAccess($request);

        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $branchFilter = $request->query('branch_id');

        $branches = Branch::with('manager:user_id,full_name,email,phone')->where('status', 'Active')->get();

        // Query consolidated branch reports
        $reportsQuery = BranchConsolidatedReport::with(['branch', 'manager']);

        if ($fromDate) {
            $reportsQuery->whereDate('period_start', '>=', $fromDate);
        }
        if ($toDate) {
            $reportsQuery->whereDate('period_end', '<=', $toDate);
        }
        if ($branchFilter && $branchFilter !== 'all') {
            $reportsQuery->where('branch_id', $branchFilter);
        }

        $consolidatedReports = $reportsQuery->orderBy('id', 'desc')->get();

        // Overview KPI metrics
        $totalBranches = $branches->count();
        $totalSubmittedReports = $consolidatedReports->count();
        $awaitingReviewCount = $consolidatedReports->where('status', 'Submitted to Super Admin')->count();
        $verifiedReportsCount = $consolidatedReports->where('status', 'Verified by Super Admin')->count();

        // Calculate combined revenue across submitted/verified reports
        $totalRegularOrders = (int) $consolidatedReports->sum('total_regular_orders');
        $totalCustomCakes = (int) $consolidatedReports->sum('total_custom_cake_orders');
        $totalRevenue = (float) $consolidatedReports->sum('total_branch_revenue');
        $totalAdvancePayments = (float) $consolidatedReports->sum('total_advance_payments');
        $totalOutstandingBalances = (float) $consolidatedReports->sum('total_outstanding_balances');
        $totalPaymentsReceived = (float) $consolidatedReports->sum('total_payments_received');

        // Branch-by-Branch Performance Table
        $branchPerformance = [];
        foreach ($branches as $branch) {
            $bReports = $consolidatedReports->where('branch_id', $branch->branch_id);
            $latestReport = $bReports->first();

            $regOrders = (int) $bReports->sum('total_regular_orders');
            $customCakes = (int) $bReports->sum('total_custom_cake_orders');
            $rev = (float) $bReports->sum('total_branch_revenue');

            $status = $latestReport ? $latestReport->status : 'No Submissions';

            $branchPerformance[] = [
                'branch_id' => $branch->branch_id,
                'branch_name' => $branch->branch_name,
                'branch_code' => $branch->branch_code,
                'city' => $branch->city,
                'manager_name' => $branch->manager?->full_name ?? 'Unassigned',
                'regular_orders' => $regOrders,
                'custom_cakes' => $customCakes,
                'total_revenue' => $rev,
                'latest_report_status' => $status,
                'latest_report_id' => $latestReport?->id,
                'reports_count' => $bReports->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'kpis' => [
                'total_branches' => $totalBranches,
                'total_submitted_reports' => $totalSubmittedReports,
                'awaiting_review_count' => $awaitingReviewCount,
                'verified_reports_count' => $verifiedReportsCount,
                'total_regular_orders' => $totalRegularOrders,
                'total_custom_cakes' => $totalCustomCakes,
                'total_revenue' => $totalRevenue,
                'total_advance_payments' => $totalAdvancePayments,
                'total_outstanding_balances' => $totalOutstandingBalances,
                'total_payments_received' => $totalPaymentsReceived,
            ],
            'branch_performance' => $branchPerformance,
            'recent_reports' => $consolidatedReports->take(20)->values(),
            'branches' => $branches->map(fn($b) => ['id' => $b->branch_id, 'name' => $b->branch_name]),
        ]);
    }

    /**
     * Deep sales inspection of a consolidated report
     */
    public function superAdminConsolidatedShow(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $report = BranchConsolidatedReport::with([
            'branch',
            'manager:user_id,full_name,email,phone',
            'superAdmin:user_id,full_name,email',
            'sellerReports.seller:user_id,full_name,email',
            'sellerReports.orders',
            'sellerReports.customCakes',
        ])->findOrFail($id);

        if ($user->role !== 'Super Admin' && (int) $report->branch_id !== (int) $user->branch_id) {
            abort(403, 'Access denied.');
        }

        return response()->json([
            'success' => true,
            'report' => $report,
        ]);
    }

    /**
     * Super Admin Action: Verify or Return for Clarification
     */
    public function superAdminVerify(Request $request, $id)
    {
        $superAdmin = $this->checkSuperAdminAccess($request);
        $report = BranchConsolidatedReport::with('branch')->findOrFail($id);

        $validated = $request->validate([
            'action' => 'required|in:verify,return',
            'super_admin_notes' => 'nullable|string|max:2000',
        ]);

        if ($validated['action'] === 'return') {
            if (empty($validated['super_admin_notes'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide clarification notes for the Branch Manager.',
                ], 422);
            }

            $report->status = 'Returned for Clarification';
            $report->super_admin_id = $superAdmin->user_id;
            $report->super_admin_notes = $validated['super_admin_notes'];
            $report->is_locked = false; // Allow manager to adjust
            $report->save();

            return response()->json([
                'success' => true,
                'message' => 'Report returned to Branch Manager for clarification.',
                'report' => $report,
            ]);
        }

        // Action == 'verify'
        $report->status = 'Verified by Super Admin';
        $report->super_admin_id = $superAdmin->user_id;
        $report->super_admin_notes = $validated['super_admin_notes'] ?? null;
        $report->verified_at = now();
        $report->is_locked = true;
        $report->save();

        return response()->json([
            'success' => true,
            'message' => 'Branch report officially verified by Super Admin.',
            'report' => $report,
        ]);
    }
}
