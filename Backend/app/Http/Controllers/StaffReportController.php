<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StaffReport;
use App\Models\User;
use App\Models\Branch;
use App\Models\AuditLog;

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
            $branchId = $request->query('branch_id');
            if ($branchId && $branchId !== 'all') {
                $query->where('branch_id', $branchId);
            }
        } elseif (in_array($currentUser->role, ['Branch Manager', 'Branch Admin', 'Main Branch Manager'])) {
            // Manager sees reports from their assigned branch
            $query->where('branch_id', $currentUser->branch_id);
        } else {
            // Staff sees own reports
            $query->where('user_id', $currentUser->user_id);
        }

        $reportType = $request->query('report_type');
        if ($reportType && $reportType !== 'all') {
            $query->where('report_type', $reportType);
        }

        $date = $request->query('date');
        if ($date) {
            $query->whereDate('created_at', $date);
        }

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date') ?: $startDate;
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
            'report_type' => 'required|in:Daily Sales,Delivery Summary,Shift Log,Task Report,Branch Performance,Price Update,Order Activity,Custom Cake Report',
            'title' => 'required|string|min:3|max:255',
            'content' => 'required|string|min:5|max:5000',
            'branch_id' => 'nullable|exists:branches,branch_id',
        ]);

        $branchId = $currentUser->branch_id ?: $validated['branch_id'];
        if (!$branchId && $currentUser->role !== 'Super Admin') {
            return response()->json(['message' => 'You must be assigned to a branch to submit a report.'], 422);
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

        return response()->json([
            'message' => 'Report review status updated successfully.',
            'report' => $report->fresh(['user', 'branch', 'submittedTo']),
        ]);
    }
}
