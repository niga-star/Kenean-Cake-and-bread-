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
use App\Models\Notification;
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

        if (!$user->branch_id) {
            $user->branch_id = Branch::where('manager_id', $user->user_id)->value('branch_id');
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

        $managerRoles = ['Branch Manager', 'Main Branch Manager', 'Branch Admin'];
        if (!in_array($user->role, $managerRoles)) {
            abort(403, 'Only Branch Managers can view, review, and manage branch sales reports.');
        }

        if (!$user->branch_id) {
            $user->branch_id = Branch::where('manager_id', $user->user_id)->value('branch_id');
        }

        if (!$user->branch_id) {
            abort(403, 'Manager must be assigned to an active branch.');
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

    /**
     * Helper to generate sequential reference: DSR-BR{branch_id}-YYYYMMDD-{SEQ}
     * with collision protection
     */
    protected function generateDsrReference($branchId, $date)
    {
        $dayStr = Carbon::parse($date)->format('Ymd');
        $prefix = "DSR-BR{$branchId}-{$dayStr}-";

        $latest = SellerDailyReport::where('report_reference', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('report_reference');

        $seq = 1;
        if ($latest && preg_match('/-(\d{3,})$/', $latest, $m)) {
            $seq = intval($m[1]) + 1;
        } else {
            $count = SellerDailyReport::where('branch_id', $branchId)
                ->whereDate('report_date', Carbon::parse($date)->toDateString())
                ->count();
            $seq = $count + 1;
        }

        do {
            $candidate = $prefix . sprintf('%03d', $seq);
            $exists = SellerDailyReport::where('report_reference', $candidate)->exists();
            if ($exists) {
                $seq++;
            }
        } while ($exists);

        return $candidate;
    }

    /**
     * Helper to generate sequential reference: BCR-BR{branch_id}-YYYYMMDD-{SEQ}
     * with collision protection
     */
    protected function generateBcrReference($branchId, $date = null)
    {
        $dayStr = Carbon::parse($date ?? now())->format('Ymd');
        $prefix = "BCR-BR{$branchId}-{$dayStr}-";

        $latest = BranchConsolidatedReport::where('report_reference', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('report_reference');

        $seq = 1;
        if ($latest && preg_match('/-(\d{3,})$/', $latest, $m)) {
            $seq = intval($m[1]) + 1;
        } else {
            $count = BranchConsolidatedReport::where('branch_id', $branchId)
                ->whereDate('created_at', Carbon::parse($date ?? now())->toDateString())
                ->count();
            $seq = $count + 1;
        }

        do {
            $candidate = $prefix . sprintf('%03d', $seq);
            $exists = BranchConsolidatedReport::where('report_reference', $candidate)->exists();
            if ($exists) {
                $seq++;
            }
        } while ($exists);

        return $candidate;
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
        // Strict branch binding: non-Super Admin cannot query other branches
        $branchId = ($user->role !== 'Super Admin') ? (int) $user->branch_id : (int) ($request->query('branch_id') ?: $user->branch_id);
        $date = $request->query('date', Carbon::today()->toDateString());

        if (!$branchId) {
            return response()->json([
                'success' => false,
                'message' => 'No branch assigned to this user.'
            ], 422);
        }

        $branch = Branch::find($branchId);

        // Financial Safeguard: Exclude orders already part of any existing seller daily report
        $reportedOrderIds = SellerReportOrder::whereNotNull('order_id')
            ->pluck('order_id')
            ->unique()
            ->toArray();

        // Fetch regular orders placed for this branch on the given date (excluding cancelled & already reported)
        $orders = Order::with('items')
            ->where('branch_id', $branchId)
            ->whereDate('placed_at', $date)
            ->where('order_status', '!=', 'Cancelled')
            ->whereNotIn('order_id', $reportedOrderIds)
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

        // Financial Safeguard: Exclude custom cakes already part of any existing seller daily report
        $reportedCakeIds = SellerReportCustomCake::whereNotNull('custom_cake_id')
            ->pluck('custom_cake_id')
            ->unique()
            ->toArray();

        // Fetch custom cakes for this branch
        $cakes = CustomCakeRequest::where('branch_id', $branchId)
            ->where(function ($q) use ($date) {
                $q->whereDate('created_at', $date)
                  ->orWhereDate('needed_by', $date);
            })
            ->where('status', '!=', 'Rejected')
            ->whereNotIn('request_id', $reportedCakeIds)
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

        // Sequential Reference Number: DSR-BR{id}-YYYYMMDD-{SEQ}
        $ref = $this->generateDsrReference($branchId, $date);

        // Cashier shift payment method reconciliation breakdown (Master Prompt Section 8 & 12)
        $cashTotal = 0.0;
        $cardTotal = 0.0;
        $telebirrTotal = 0.0;
        $otherPaymentTotal = 0.0;

        foreach ($orders as $order) {
            $paymentMethod = $order->payment?->payment_method ?? 'Cash';
            $amount = (float) $order->total_amount;
            if (strcasecmp($paymentMethod, 'Cash') === 0 || strcasecmp($paymentMethod, 'Pay on delivery') === 0 || strcasecmp($paymentMethod, 'Pay on pickup') === 0) {
                $cashTotal += $amount;
            } elseif (strcasecmp($paymentMethod, 'Card') === 0 || strcasecmp($paymentMethod, 'POS Card') === 0) {
                $cardTotal += $amount;
            } elseif (strcasecmp($paymentMethod, 'Telebirr') === 0) {
                $telebirrTotal += $amount;
            } else {
                $otherPaymentTotal += $amount;
            }
        }

        $cakeAdvanceTotal = array_sum(array_column($customCakeRows, 'advance_payment'));

        $shiftSummary = [
            'orders_count' => count($orders),
            'cash_total' => round($cashTotal, 2),
            'card_total' => round($cardTotal, 2),
            'telebirr_total' => round($telebirrTotal, 2),
            'other_total' => round($otherPaymentTotal, 2),
            'cake_deposits_total' => round($cakeAdvanceTotal, 2),
            'total_cash_collected' => round($cashTotal + $cakeAdvanceTotal, 2),
            'total_booked_sales' => round($cashTotal + $cardTotal + $telebirrTotal + $otherPaymentTotal + array_sum(array_column($customCakeRows, 'total_price')), 2),
        ];

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
            'shift_summary' => $shiftSummary,
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

        // Scoping: Only Branch Managers view reports for their branch. Sellers see own. Super Admin does not view seller reports.
        if ($user->role === 'Super Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Seller reports are managed exclusively by Branch Managers for their assigned branch.',
            ], 403);
        }

        $userBranchId = $user->branch_id ?? ($user ? Branch::where('manager_id', $user->user_id)->value('branch_id') : null);
        if (in_array($user->role, ['Branch Manager', 'Main Branch Manager', 'Branch Admin'])) {
            $query->where('branch_id', $userBranchId ?: -1);
        } else {
            // Seller/Cashier sees only their own reports
            $query->where('seller_id', $user->user_id);
        }

        if ($request->query('status')) {
            $st = $request->query('status');
            if ($st === 'Pending Verification' || $st === 'Submitted') {
                $query->whereIn('status', ['Pending Verification', 'Submitted']);
            } elseif ($st === 'Returned' || $st === 'Rejected' || $st === 'Returned for Correction') {
                $query->whereIn('status', ['Returned', 'Rejected', 'Returned for Correction']);
            } else {
                $query->where('status', $st);
            }
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
            'customCakes.systemCustomCake.images',
            'consolidatedReport',
        ])->findOrFail($id);

        // Security check: Only Branch Managers view reports for their branch; sellers view own. Super Admin does not view seller reports.
        if ($user->role === 'Super Admin') {
            abort(403, 'Seller reports are managed exclusively by Branch Managers for their assigned branch.');
        }

        $userBranchId = $user->branch_id ?? ($user ? Branch::where('manager_id', $user->user_id)->value('branch_id') : null);
        if (in_array($user->role, ['Branch Manager', 'Main Branch Manager', 'Branch Admin'])) {
            if ((int) $report->branch_id !== (int) $userBranchId) {
                abort(403, 'Access denied: You can only view reports for your assigned branch.');
            }
        } else {
            if ((int) $report->seller_id !== (int) $user->user_id) {
                abort(403, 'Access denied: You can only view your own reports.');
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

        // Strict branch binding: non-Super Admin cannot tamper with branch_id
        $branchId = ($user->role !== 'Super Admin') ? (int) $user->branch_id : (int) ($request->input('branch_id') ?: $user->branch_id);
        if (!$branchId) {
            return response()->json(['success' => false, 'message' => 'Branch ID is required.'], 422);
        }

        $validated = $request->validate([
            'report_date' => 'required|date',
            'report_reference' => 'nullable|string',
            'action' => 'required|in:draft,submit',
            'orders' => 'present|array',
            'orders.*.order_id' => 'nullable|integer',
            'orders.*.order_number' => 'required|string',
            'orders.*.product_name' => 'required|string',
            'orders.*.category' => 'nullable|string',
            'orders.*.quantity' => 'required|numeric|min:1',
            'orders.*.unit_price' => 'required|numeric|min:0',
            'orders.*.order_status' => 'nullable|string',
            'orders.*.payment_status' => 'nullable|string',
            'orders.*.customer_info' => 'nullable|string',
            'custom_cakes' => 'present|array',
            'custom_cakes.*.custom_cake_id' => 'nullable|integer',
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
        // Sequential reference with collision protection
        $ref = !empty($validated['report_reference']) ? $validated['report_reference'] : $this->generateDsrReference($branchId, $reportDate);

        // Status determination
        $isSubmit = $validated['action'] === 'submit';
        $status = $isSubmit ? 'Submitted' : 'Draft';

        DB::beginTransaction();
        try {
            // Guard against duplicate orders in submission
            foreach ($validated['orders'] as $row) {
                if (!empty($row['order_id'])) {
                    $alreadyExists = SellerReportOrder::where('order_id', $row['order_id'])->exists();
                    if ($alreadyExists) {
                        throw new \Exception("Order #" . ($row['order_number'] ?? $row['order_id']) . " has already been included in another daily sales report.");
                    }
                }
            }

            // Guard against duplicate custom cakes in submission
            foreach ($validated['custom_cakes'] as $row) {
                if (!empty($row['custom_cake_id'])) {
                    $alreadyExists = SellerReportCustomCake::where('custom_cake_id', $row['custom_cake_id'])->exists();
                    if ($alreadyExists) {
                        throw new \Exception("Custom Cake #" . ($row['cake_order_number'] ?? $row['custom_cake_id']) . " has already been included in another daily sales report.");
                    }
                }
            }

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

            if ($isSubmit) {
                // Find assigned Branch Manager for this branch
                $branch = Branch::find($branchId);
                $managerId = $branch?->manager_id ?: User::where('branch_id', $branchId)
                    ->whereIn('role', ['Branch Manager', 'Main Branch Manager', 'Branch Admin'])
                    ->value('user_id');

                if ($managerId) {
                    Notification::create([
                        'user_id' => $managerId,
                        'branch_id' => $branchId,
                        'category' => 'Reports',
                        'title' => "New Daily Sales Report: {$ref}",
                        'message' => "Seller {$user->full_name} submitted daily sales report {$ref} (ETB " . number_format($report->combined_daily_revenue, 2) . ") for your review.",
                        'link' => '/admin/manager-reports',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isSubmit ? 'Daily sales report submitted to your Branch Manager for review and approval successfully.' : 'Daily sales report saved as draft.',
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

        if ($user->role !== 'Super Admin' && (int) $report->branch_id !== (int) $user->branch_id) {
            abort(403, 'Access denied: You can only update reports for your assigned branch.');
        }

        if (!in_array($report->status, ['Draft', 'Returned', 'Returned for Correction', 'Rejected'])) {
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
            // Guard against duplicate orders in update (excluding this report's previous lines)
            foreach ($validated['orders'] as $row) {
                if (!empty($row['order_id'])) {
                    $alreadyExists = SellerReportOrder::where('order_id', $row['order_id'])
                        ->where('report_id', '!=', $report->id)
                        ->exists();
                    if ($alreadyExists) {
                        throw new \Exception("Order #" . ($row['order_number'] ?? $row['order_id']) . " has already been included in another daily sales report.");
                    }
                }
            }

            // Guard against duplicate custom cakes in update
            foreach ($validated['custom_cakes'] as $row) {
                if (!empty($row['custom_cake_id'])) {
                    $alreadyExists = SellerReportCustomCake::where('custom_cake_id', $row['custom_cake_id'])
                        ->where('report_id', '!=', $report->id)
                        ->exists();
                    if ($alreadyExists) {
                        throw new \Exception("Custom Cake #" . ($row['cake_order_number'] ?? $row['custom_cake_id']) . " has already been included in another daily sales report.");
                    }
                }
            }

            if (!empty($validated['report_date'])) {
                $report->report_date = Carbon::parse($validated['report_date'])->toDateString();
            }

            $report->status = $isSubmit ? 'Submitted' : 'Draft';
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

            if ($isSubmit) {
                $branch = Branch::find($report->branch_id);
                $managerId = $branch?->manager_id ?: User::where('branch_id', $report->branch_id)
                    ->whereIn('role', ['Branch Manager', 'Main Branch Manager', 'Branch Admin'])
                    ->value('user_id');

                if ($managerId) {
                    Notification::create([
                        'user_id' => $managerId,
                        'branch_id' => $report->branch_id,
                        'category' => 'Reports',
                        'title' => "Re-submitted Daily Sales Report: {$report->report_reference}",
                        'message' => "Seller {$user->full_name} has re-submitted daily sales report {$report->report_reference} (ETB " . number_format($report->combined_daily_revenue, 2) . ") for your review.",
                        'link' => '/admin/manager-reports',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isSubmit ? 'Report re-submitted to your Branch Manager for review and approval.' : 'Draft updated successfully.',
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
     * Branch Manager reviews a seller report: Approve directly or Reject with reason
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
            'action' => 'required|in:approve,return,reject',
            'manager_notes' => 'nullable|string|max:1000',
        ]);

        if ($validated['action'] === 'return' || $validated['action'] === 'reject') {
            if (empty($validated['manager_notes'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide a reason / correction note explaining what needs to be fixed.',
                ], 422);
            }

            $report->status = 'Returned';
            $report->manager_id = $manager->user_id;
            $report->manager_notes = $validated['manager_notes'];
            $report->returned_at = now();
            $report->save();

            // Dispatch notification directly to the seller (Strictly once)
            Notification::create([
                'user_id' => $report->seller_id,
                'branch_id' => $report->branch_id,
                'category' => 'Reports',
                'title' => "Report Returned: {$report->report_reference}",
                'message' => "Manager note: {$validated['manager_notes']}",
                'link' => '/admin/seller-reports',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report has been returned to seller with correction notes.',
                'report' => $report,
            ]);
        }

        // Action == 'approve'
        $report->status = 'Approved';
        $report->manager_id = $manager->user_id;
        $report->manager_notes = $validated['manager_notes'] ?? null;
        $report->verified_at = now();
        $report->save();

        // Dispatch notification directly to the seller (Strictly once)
        Notification::create([
            'user_id' => $report->seller_id,
            'branch_id' => $report->branch_id,
            'category' => 'Reports',
            'title' => "Report Approved: {$report->report_reference}",
            'message' => "Your daily sales report was verified and approved by {$manager->full_name}.",
            'link' => '/admin/seller-reports',
        ]);

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
        // Strict branch binding: non-Super Admin cannot access other branches
        $branchId = ($manager->role !== 'Super Admin') ? (int) $manager->branch_id : (int) ($request->query('branch_id') ?: $manager->branch_id);

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

        // Sequential reference with collision protection
        $suggestedRef = $this->generateBcrReference($branchId, $fromDate);

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
        // Strict branch binding
        $branchId = ($manager->role !== 'Super Admin') ? (int) $manager->branch_id : (int) ($request->input('branch_id') ?: $manager->branch_id);

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

        // Sequential reference with collision protection
        $ref = $validated['report_reference'] ?: $this->generateBcrReference($branchId, $start);

        DB::beginTransaction();
        try {
            $consolidated = new BranchConsolidatedReport();
            $consolidated->report_reference = $ref;
            $consolidated->branch_id = $branchId;
            $consolidated->manager_id = $manager->user_id;
            $consolidated->period_type = $validated['period_type'];
            $consolidated->period_start = $start;
            $consolidated->period_end = $end;
            $consolidated->status = 'Finalized';
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

            // Notify Super Admin with direct link to view the submitted report
            $superAdmins = User::where('role', 'Super Admin')->get();
            foreach ($superAdmins as $superAdmin) {
                Notification::create([
                    'user_id' => $superAdmin->user_id,
                    'branch_id' => $branchId,
                    'category' => 'Reports',
                    'title' => "New Branch Sales Report: {$ref}",
                    'message' => "Manager {$manager->full_name} submitted consolidated sales report {$ref} ({$consolidated->period_type}) for your review.",
                    'link' => "/admin/manager-reports?report_id={$consolidated->id}",
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Branch sales report consolidated and finalized successfully.',
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

        $query = BranchConsolidatedReport::with(['branch', 'manager:user_id,full_name,email'])
            ->orderBy('period_end', 'desc')
            ->orderBy('id', 'desc');

        if ($user->role !== 'Super Admin') {
            $userBranchId = $user->branch_id ?? Branch::where('manager_id', $user->user_id)->value('branch_id');
            $query->where('branch_id', $userBranchId ?: -1);
        } elseif ($request->filled('branch_id') && $request->branch_id !== 'all') {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->query('status')) {
            $st = $request->query('status');
            if ($st === 'Pending Verification' || $st === 'Submitted') {
                $query->whereIn('status', ['Pending Verification', 'Submitted']);
            } elseif ($st === 'Returned' || $st === 'Rejected' || $st === 'Returned for Correction') {
                $query->whereIn('status', ['Returned', 'Rejected', 'Returned for Correction']);
            } else {
                $query->where('status', $st);
            }
        }

        $reports = $query->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }

    /**
     * Inspect a consolidated report for the manager's branch
     */
    public function managerConsolidatedShow(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $report = BranchConsolidatedReport::with([
            'branch',
            'manager:user_id,full_name,email,phone',
            'sellerReports.seller:user_id,full_name,email',
            'sellerReports.orders',
            'sellerReports.customCakes.systemCustomCake.images',
        ])->findOrFail($id);

        if ($user->role !== 'Super Admin') {
            $userBranchId = $user->branch_id ?? Branch::where('manager_id', $user->user_id)->value('branch_id');
            if ((int) $report->branch_id !== (int) $userBranchId) {
                abort(403, 'Access denied: You can only view reports for your assigned branch.');
            }
        }

        return response()->json([
            'success' => true,
            'report' => $report,
        ]);
    }

    /**
     * Re-submit a consolidated report that was returned by Super Admin for clarification
     */
    public function managerConsolidatedResubmit(Request $request, $id)
    {
        $manager = $this->checkManagerAccess($request);
        $report = BranchConsolidatedReport::findOrFail($id);

        if ($manager->role !== 'Super Admin' && (int) $report->branch_id !== (int) $manager->branch_id) {
            abort(403, 'Access denied.');
        }

        if ($report->status !== 'Returned for Clarification') {
            return response()->json([
                'success' => false,
                'message' => 'Only reports with status "Returned for Clarification" can be re-submitted.',
            ], 422);
        }

        $validated = $request->validate([
            'manager_notes' => 'required|string|max:2000',
            'confirmed_accuracy' => 'required|accepted',
        ]);

        $report->manager_notes = $validated['manager_notes'];
        $report->confirmed_accuracy = true;
        $report->status = 'Submitted to Super Admin';
        $report->is_locked = true;
        $report->submitted_at = now();
        $report->save();

        return response()->json([
            'success' => true,
            'message' => 'Consolidated branch report updated and re-submitted to Super Admin for verification.',
            'report' => $report->load(['branch', 'manager']),
        ]);
    }

    // =========================================================================
    // LEVEL 3: SUPER ADMIN ENDPOINTS (DEPRECATED: Branch sales reports are managed at branch level)
    // =========================================================================

    /**
     * Super Admin Multi-Branch Sales & Order Reports Overview (Deprecated: Branch sales reports are managed at branch level)
     */
    public function superAdminOverview(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Branch sales reports are no longer submitted to Super Admin. Branch Managers are responsible for managing and reviewing all sales reports within their branch.',
        ], 403);
    }

    /**
     * Delete seller daily report (Draft only - financial records protected)
     */
    public function sellerReportDestroy(Request $request, $id)
    {
        $user = $this->checkSellerAccess($request);
        $report = SellerDailyReport::findOrFail($id);

        if ($user->role !== 'Super Admin' && (int) $report->seller_id !== (int) $user->user_id) {
            abort(403, 'Access denied: You can only delete your own draft reports.');
        }

        // Financial Integrity: protect non-draft records
        if ($report->status !== 'Draft') {
            return response()->json([
                'success' => false,
                'message' => 'Financial record is protected: Cannot delete a daily report with status "' . $report->status . '". Reports in verification or approved cannot be deleted for audit compliance.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $report->orders()->delete();
            $report->customCakes()->delete();
            $report->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Draft daily sales report deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Deep sales inspection of a consolidated report
     */
    public function superAdminConsolidatedShow(Request $request, $id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Branch sales reports are no longer submitted to Super Admin. Branch Managers are responsible for managing and reviewing all sales reports within their branch.',
        ], 403);
    }

    /**
     * Super Admin Action: Verify or Return for Clarification (Deprecated)
     */
    public function superAdminVerify(Request $request, $id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Branch sales reports are no longer submitted to Super Admin. Branch Managers are responsible for managing and reviewing all sales reports within their branch.',
        ], 403);
    }

    /**
     * Super Admin multi-branch sales & revenue summary across approved daily reports
     */
    public function adminBranchSummary(Request $request)
    {
        $this->checkSuperAdminAccess($request);

        $branches = Branch::all();
        $branchSummaries = [];

        $platformBookedSales = 0.0;
        $platformCashCollected = 0.0;
        $platformApprovedReports = 0;

        foreach ($branches as $branch) {
            $approvedReports = SellerDailyReport::where('branch_id', $branch->branch_id)
                ->where('status', 'Approved')
                ->get();

            $pendingCount = SellerDailyReport::where('branch_id', $branch->branch_id)
                ->whereIn('status', ['Submitted', 'Pending Verification'])
                ->count();

            $bookedSales = (float) $approvedReports->sum('combined_daily_revenue');
            $cashCollected = (float) $approvedReports->sum('total_payments_received');
            $approvedCount = $approvedReports->count();
            $regularRevenue = (float) $approvedReports->sum('regular_order_revenue');
            $customCakeRevenue = (float) $approvedReports->sum('custom_cake_revenue');

            $platformBookedSales += $bookedSales;
            $platformCashCollected += $cashCollected;
            $platformApprovedReports += $approvedCount;

            $branchSummaries[] = [
                'branch_id' => $branch->branch_id,
                'branch_name' => $branch->branch_name,
                'branch_code' => $branch->branch_code,
                'branch_type' => $branch->branch_type,
                'total_booked_sales' => round($bookedSales, 2),
                'total_cash_collected' => round($cashCollected, 2),
                'approved_reports_count' => $approvedCount,
                'pending_reports_count' => $pendingCount,
                'regular_order_revenue' => round($regularRevenue, 2),
                'custom_cake_revenue' => round($customCakeRevenue, 2),
            ];
        }

        return response()->json([
            'success' => true,
            'platform_summary' => [
                'total_booked_sales' => round($platformBookedSales, 2),
                'total_cash_collected' => round($platformCashCollected, 2),
                'total_approved_reports' => $platformApprovedReports,
                'branches_count' => $branches->count(),
            ],
            'branches' => $branchSummaries,
        ]);
    }
}

