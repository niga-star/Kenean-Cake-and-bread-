<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StaffReport;
use App\Models\User;
use App\Models\Branch;
use App\Models\AuditLog;
use App\Models\Notification;
use Carbon\Carbon;

class StaffReportController extends Controller
{
    /**
     * List reports following the reporting hierarchy:
     * - Staff: view own reports
     * - Branch Manager: view reports from their branch staff + reports they submitted
     * - Super Admin: view all reports across all branches
     */
    public function index(Request $request)
    {
        $currentUser = $request->user();
        if (!$currentUser) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $query = StaffReport::with(['user', 'branch', 'submittedTo']);

        if ($currentUser->role === 'Super Admin') {
            // Super Admin only receives reports submitted or forwarded by Branch Managers (no direct individual staff shift logs)
            $query->where(function ($q) use ($currentUser) {
                $q->where('submitted_to_id', $currentUser->user_id)
                  ->orWhere('status', 'Forwarded to Super Admin')
                  ->orWhereHas('user', function ($uq) {
                      $uq->whereIn('role', ['Branch Manager', 'Branch Admin', 'Main Branch Manager']);
                  });
            });

            $branchId = $request->query('branch_id');
            if ($branchId && $branchId !== 'all') {
                $query->where('branch_id', $branchId);
            }
        } else {
            // Branch privacy enforcement: Branch Manager and Branch Staff only access their assigned branch
            $requestedBranch = $request->query('branch_id');
            if ($requestedBranch && $requestedBranch !== 'all' && (int)$requestedBranch !== (int)$currentUser->branch_id) {
                return response()->json(['message' => 'Access denied: You cannot view reports from other branches.'], 403);
            }
            $query->where('branch_id', $currentUser->branch_id);
        }

        $reportType = $request->query('report_type');
        if ($reportType && $reportType !== 'all') {
            $query->where('report_type', $reportType);
        }

        $dateRange = $request->query('date_range');
        if ($dateRange) {
            if ($dateRange === 'today') {
                $query->whereDate('created_at', Carbon::today());
            } elseif ($dateRange === 'this_week') {
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } elseif ($dateRange === 'this_month') {
                $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
            }
        }

        $date = $request->query('date');
        if ($date) {
            $query->whereDate('created_at', $date);
        }

        $startDate = $request->query('start_date') ?: $request->query('from_date');
        $endDate = $request->query('end_date') ?: ($request->query('to_date') ?: $startDate);
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
        }

        $reports = $query->orderByDesc('created_at')->get();

        return response()->json($reports);
    }

    /**
     * Submit a staff report.
     * - Staff submits report to their Branch Manager
     * - Branch Manager submits consolidated report to Super Admin
     */
    public function store(Request $request)
    {
        $currentUser = $request->user();
        if (!$currentUser) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($currentUser->role === 'Customer') {
            return response()->json(['message' => 'Access denied: Customers cannot submit staff reports.'], 403);
        }

        $validated = $request->validate([
            'report_type' => 'required|in:Daily Sales,Delivery Summary,Shift Log,Task Report,Branch Performance,Price Update,Order Activity,Custom Cake Report,Product Information,Product Info Report',
            'title' => 'required|string|min:3|max:255',
            'content' => 'required|string|min:5|max:5000',
            'branch_id' => 'nullable|exists:branches,branch_id',
        ]);

        $branchId = $currentUser->branch_id ?: ($validated['branch_id'] ?? null);
        if (!$branchId && $currentUser->role !== 'Super Admin') {
            return response()->json(['message' => 'You must be assigned to a branch to submit a report.'], 422);
        }

        if ($currentUser->role !== 'Super Admin' && !empty($validated['branch_id']) && (int)$validated['branch_id'] !== (int)$currentUser->branch_id) {
            return response()->json(['message' => 'Access denied: You can only submit reports for your assigned branch.'], 403);
        }

        // Determine hierarchy destination:
        // If staff: submitted to Branch Manager
        // If branch manager: submitted to Super Admin
        $submittedToId = null;
        if (in_array($currentUser->role, ['Branch Manager', 'Branch Admin', 'Main Branch Manager'])) {
            $superAdmin = User::where('role', 'Super Admin')->first();
            $submittedToId = $superAdmin?->user_id;
        } else {
            $branch = Branch::find($branchId);
            $submittedToId = $branch?->manager_id;
        }

        $report = StaffReport::create([
            'user_id' => $currentUser->user_id,
            'branch_id' => $branchId ?: 1,
            'report_type' => $validated['report_type'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'submitted_to_id' => $submittedToId,
            'status' => 'Submitted',
        ]);

        AuditLog::create([
            'user_id' => $currentUser->user_id,
            'user_name' => $currentUser->full_name . " ({$currentUser->role})",
            'action' => 'Report submitted',
            'record' => $report->title,
            'entity_type' => 'StaffReport',
            'entity_id' => (string) $report->report_id,
            'old_value' => '—',
            'new_value' => "{$report->report_type} · " . ($report->branch?->branch_name ?? 'Branch'),
            'created_at' => now(),
        ]);

        if ($submittedToId) {
            $isManager = in_array($currentUser->role, ['Branch Manager', 'Branch Admin', 'Main Branch Manager']);
            Notification::create([
                'user_id' => $submittedToId,
                'branch_id' => $branchId ?: 1,
                'category' => $isManager ? 'Reports' : 'Staff Reports',
                'title' => $isManager ? "New Branch Report: {$report->title}" : "New Staff Report: {$report->title}",
                'message' => $isManager
                    ? "Branch Manager {$currentUser->full_name} submitted {$report->report_type} for review."
                    : "Staff member {$currentUser->full_name} submitted a {$report->report_type} for review.",
                'link' => $isManager ? "/admin/manager-reports?report_id={$report->report_id}" : '/admin/staff-reports',
            ]);
        }

        return response()->json([
            'message' => 'Report submitted successfully.',
            'report' => $report->load(['user', 'branch', 'submittedTo']),
        ], 201);
    }

    /**
     * Review or forward report.
     * Branch Manager can review staff reports and forward them to Super Admin.
     * Super Admin can mark them as Reviewed / Resolved.
     */
    public function review(Request $request, $id)
    {
        $currentUser = $request->user();
        if (!$currentUser || in_array($currentUser->role, ['Customer', 'Branch Staff', 'Branch Seller', 'Servant', 'Delivery Staff'])) {
            return response()->json(['message' => 'Access denied: Only managers and Super Admin can review staff reports.'], 403);
        }

        $report = StaffReport::findOrFail($id);

        // Branch manager can only review reports from their branch
        if (in_array($currentUser->role, ['Branch Manager', 'Branch Admin']) && $report->branch_id !== $currentUser->branch_id) {
            return response()->json(['message' => 'Access denied: You cannot review reports from other branches.'], 403);
        }

        $status = $request->input('status');
        $action = $request->input('action');
        if (!$status && $action) {
            $actionLower = strtolower($action);
            if ($actionLower === 'forward' || $actionLower === 'forwarded' || $actionLower === 'forwarded to super admin') {
                $status = 'Forwarded to Super Admin';
            } elseif ($actionLower === 'resolve' || $actionLower === 'resolved') {
                $status = 'Resolved';
            } elseif ($actionLower === 'review' || $actionLower === 'reviewed') {
                $status = 'Reviewed';
            }
        }
        if ($status) {
            $request->merge(['status' => $status]);
        }

        $validated = $request->validate([
            'status' => 'required|in:Reviewed,Forwarded to Super Admin,Resolved',
            'manager_notes' => 'nullable|string|max:1000',
        ]);

        $report->status = $validated['status'];
        if (isset($validated['manager_notes'])) {
            $report->manager_notes = $validated['manager_notes'];
        }
        $report->save();

        AuditLog::create([
            'user_id' => $currentUser->user_id,
            'user_name' => $currentUser->full_name . " ({$currentUser->role})",
            'action' => "Report {$report->status}",
            'record' => $report->title,
            'entity_type' => 'StaffReport',
            'entity_id' => (string) $report->report_id,
            'old_value' => 'Submitted',
            'new_value' => $report->status . ($report->manager_notes ? " - {$report->manager_notes}" : ''),
            'created_at' => now(),
        ]);

        if ($report->status === 'Forwarded to Super Admin') {
            $superAdmins = User::where('role', 'Super Admin')->get();
            foreach ($superAdmins as $superAdmin) {
                Notification::create([
                    'user_id' => $superAdmin->user_id,
                    'branch_id' => $report->branch_id,
                    'category' => 'Reports',
                    'title' => "Branch Report Forwarded: {$report->title}",
                    'message' => "Manager {$currentUser->full_name} forwarded report '{$report->title}' for your review.",
                    'link' => "/admin/manager-reports?report_id={$report->report_id}",
                ]);
            }
        }

        if ($report->user_id) {
            Notification::create([
                'user_id' => $report->user_id,
                'branch_id' => $report->branch_id,
                'category' => 'Staff Reports',
                'title' => "Staff Report {$report->status}",
                'message' => "Your report '{$report->title}' was reviewed by {$currentUser->full_name} ({$report->status}).",
                'link' => '/admin/staff-reports',
            ]);
        }

        return response()->json([
            'message' => 'Report review status updated successfully.',
            'report' => $report->fresh(['user', 'branch', 'submittedTo']),
        ]);
    }
}
