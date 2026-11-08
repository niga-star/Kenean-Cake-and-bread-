<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use App\Models\SalaryCorrectionRequest;
use App\Models\AuditLog;

class SalaryController extends Controller
{
    /**
     * Get the logged-in employee's own salary and account info.
     * Section 8 requirement: Each employee can view their own profile, including name,
     * Amharic name, phone number, role, branch, and salary information according to their permissions.
     */
    public function myAccount(Request $request)
    {
        $currentUser = $request->user();
        if (!$currentUser) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = User::with('branch')->find($currentUser->user_id);
        $recentRequests = SalaryCorrectionRequest::where('user_id', $user->user_id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return response()->json([
            'user' => [
                'user_id' => $user->user_id,
                'full_name' => $user->full_name,
                'full_name_am' => $user->full_name_am,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'branch_id' => $user->branch_id,
                'branch_name' => $user->branch ? $user->branch->branch_name : 'All branches',
                'salary' => (float) $user->salary,
                'employment_status' => $user->employment_status ?: $user->status,
                'status' => $user->status,
                'hire_date' => $user->hire_date ? $user->hire_date->format('Y-m-d') : null,
            ],
            'recent_requests' => $recentRequests,
        ]);
    }

    /**
     * Get salary overview, KPIs, and branch-by-branch wage expenses.
     * Accessible to Super Admin.
     */
    public function index(Request $request)
    {
        $currentUser = $request->user();
        if (!$currentUser || $currentUser->role !== 'Super Admin') {
            return response()->json(['message' => 'Access denied: Only Super Admin can view comprehensive salary management.'], 403);
        }

        $branchId = $request->query('branch_id');
        $role = $request->query('role');

        $branches = Branch::with(['users' => function ($q) {
            $q->where('role', '!=', 'Customer');
        }])->get();

        $branchBreakdown = $branches->map(function ($b) {
            $staffCount = $b->users->count();
            $totalWage = (float) $b->users->sum('salary');
            $avgWage = $staffCount > 0 ? round($totalWage / $staffCount, 2) : 0;
            $manager = $b->users->first(function ($u) {
                return in_array($u->role, ['Branch Manager', 'Branch Admin', 'Main Branch Manager']);
            });

            return [
                'branch_id' => $b->branch_id,
                'branch_name' => $b->branch_name,
                'branch_code' => $b->branch_code,
                'manager_name' => $manager?->full_name ?? ($b->branch_type === 'Main' ? 'Dawit Tadesse' : 'Assigned'),
                'staff_count' => $staffCount,
                'total_wage' => $totalWage,
                'avg_wage' => $avgWage,
                'max_staff' => $b->max_staff,
                'max_managers' => $b->max_managers,
            ];
        });

        $query = User::with('branch')->where('role', '!=', 'Customer');

        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        if ($role && $role !== 'all') {
            $query->where('role', $role);
        }

        $employees = $query->orderBy('branch_id')->get()->map(function ($u) {
            return [
                'user_id' => $u->user_id,
                'full_name' => $u->full_name,
                'full_name_am' => $u->full_name_am,
                'email' => $u->email,
                'phone' => $u->phone,
                'role' => $u->role,
                'branch_id' => $u->branch_id,
                'branch_name' => $u->branch ? $u->branch->branch_name : 'Headquarters',
                'salary' => (float) $u->salary,
                'employment_status' => $u->employment_status ?: $u->status,
                'status' => $u->status,
                'hire_date' => $u->hire_date ? $u->hire_date->format('Y-m-d') : null,
            ];
        });

        $totalBusinessWage = (float) User::where('role', '!=', 'Customer')->sum('salary');
        $totalEmployees = User::where('role', '!=', 'Customer')->count();
        $overallAvgWage = $totalEmployees > 0 ? round($totalBusinessWage / $totalEmployees, 2) : 0;

        return response()->json([
            'summary' => [
                'total_wage_expenses' => $totalBusinessWage,
                'total_employees' => $totalEmployees,
                'average_wage' => $overallAvgWage,
                'currency' => 'ETB',
            ],
            'kpis' => [
                'total_monthly_payroll' => $totalBusinessWage,
                'total_staff' => $totalEmployees,
                'average_salary' => $overallAvgWage,
                'currency' => 'ETB',
            ],
            'branch_breakdown' => $branchBreakdown,
            'employees' => $employees,
        ]);
    }

    /**
     * Update an employee's salary.
     * Strictly Super Admin only.
     */
    public function updateSalary(Request $request, $userId)
    {
        $currentUser = $request->user();
        if (!$currentUser || $currentUser->role !== 'Super Admin') {
            return response()->json(['message' => 'Access denied: Only Super Admin can modify employee salaries.'], 403);
        }

        $validated = $request->validate([
            'salary' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($userId);
        $oldSalary = (float) $user->salary;
        $newSalary = (float) $validated['salary'];

        $user->update([
            'salary' => $newSalary,
        ]);

        AuditLog::create([
            'user_id' => $currentUser->user_id,
            'user_name' => $currentUser->full_name . ' (Super Admin)',
            'action' => 'Salary updated',
            'record' => $user->full_name,
            'entity_type' => 'Salary',
            'entity_id' => (string) $user->user_id,
            'old_value' => "ETB " . number_format($oldSalary, 2),
            'new_value' => "ETB " . number_format($newSalary, 2) . (!empty($validated['reason']) ? " ({$validated['reason']})" : ""),
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Salary updated successfully.',
            'user' => [
                'user_id' => $user->user_id,
                'full_name' => $user->full_name,
                'salary' => (float) $user->salary,
            ],
        ]);
    }

    /**
     * Submit a salary correction request.
     * Available to Branch Managers and staff.
     */
    public function submitCorrectionRequest(Request $request)
    {
        $currentUser = $request->user();
        if (!$currentUser) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'requested_salary' => 'nullable|numeric|min:0',
            'reason' => 'required|string|min:5|max:1000',
        ]);

        $correctionRequest = SalaryCorrectionRequest::create([
            'user_id' => $currentUser->user_id,
            'branch_id' => $currentUser->branch_id,
            'current_salary' => (float) $currentUser->salary,
            'requested_salary' => isset($validated['requested_salary']) ? (float) $validated['requested_salary'] : null,
            'reason' => $validated['reason'],
            'status' => 'Pending',
        ]);

        AuditLog::create([
            'user_id' => $currentUser->user_id,
            'user_name' => $currentUser->full_name . " ({$currentUser->role})",
            'action' => 'Salary correction requested',
            'record' => $currentUser->full_name,
            'entity_type' => 'SalaryRequest',
            'entity_id' => (string) $correctionRequest->request_id,
            'old_value' => "Current: ETB " . number_format($currentUser->salary, 2),
            'new_value' => isset($validated['requested_salary']) ? ("Requested: ETB " . number_format($validated['requested_salary'], 2)) : "Correction requested",
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Your salary correction request has been submitted to the Super Admin.',
            'request' => $correctionRequest->load('user', 'branch'),
        ], 201);
    }

    /**
     * List salary correction requests.
     */
    public function listCorrectionRequests(Request $request)
    {
        $currentUser = $request->user();
        if (!$currentUser) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $query = SalaryCorrectionRequest::with(['user', 'branch', 'reviewer']);

        if ($currentUser->role === 'Super Admin') {
            // Super admin sees all requests
            $status = $request->query('status');
            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }
            $branchId = $request->query('branch_id');
            if ($branchId && $branchId !== 'all') {
                $query->where('branch_id', $branchId);
            }
        } elseif (in_array($currentUser->role, ['Branch Manager', 'Branch Admin', 'Main Branch Manager'])) {
            // Branch manager sees requests from their assigned branch
            $query->where('branch_id', $currentUser->branch_id);
        } else {
            // Staff sees only their own requests
            $query->where('user_id', $currentUser->user_id);
        }

        $requests = $query->orderByDesc('created_at')->get();

        return response()->json($requests);
    }

    /**
     * Review / Approve / Reject salary correction request.
     * Strictly Super Admin only.
     */
    public function reviewCorrectionRequest(Request $request, $id)
    {
        $currentUser = $request->user();
        if (!$currentUser || $currentUser->role !== 'Super Admin') {
            return response()->json(['message' => 'Access denied: Only Super Admin can approve or reject salary correction requests.'], 403);
        }

        $status = $request->input('status');
        $action = $request->input('action');
        if (!$status && $action) {
            $status = in_array(strtolower($action), ['approve', 'approved']) ? 'Approved' : 'Rejected';
        }
        $request->merge(['status' => $status]);

        $validated = $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'admin_notes' => 'nullable|string|max:500',
            'approved_salary' => 'nullable|numeric|min:0',
        ]);

        $correctionRequest = SalaryCorrectionRequest::with('user')->findOrFail($id);
        $user = $correctionRequest->user;

        $correctionRequest->status = $validated['status'];
        $correctionRequest->admin_notes = $validated['admin_notes'] ?? null;
        $correctionRequest->reviewed_by = $currentUser->user_id;
        $correctionRequest->reviewed_at = now();
        $correctionRequest->save();

        // If approved and approved_salary or requested_salary is provided, update employee's salary automatically!
        if ($validated['status'] === 'Approved') {
            $newSalary = $validated['approved_salary'] ?? $correctionRequest->requested_salary;
            if ($newSalary !== null && $newSalary > 0) {
                $oldSalary = (float) $user->salary;
                $user->update(['salary' => $newSalary]);

                AuditLog::create([
                    'user_id' => $currentUser->user_id,
                    'user_name' => $currentUser->full_name . ' (Super Admin)',
                    'action' => 'Salary updated via approved request',
                    'record' => $user->full_name,
                    'entity_type' => 'Salary',
                    'entity_id' => (string) $user->user_id,
                    'old_value' => "ETB " . number_format($oldSalary, 2),
                    'new_value' => "ETB " . number_format($newSalary, 2),
                    'created_at' => now(),
                ]);
            }
        }

        AuditLog::create([
            'user_id' => $currentUser->user_id,
            'user_name' => $currentUser->full_name . ' (Super Admin)',
            'action' => "Salary request {$validated['status']}",
            'record' => $user->full_name,
            'entity_type' => 'SalaryRequest',
            'entity_id' => (string) $correctionRequest->request_id,
            'old_value' => 'Pending',
            'new_value' => $validated['status'] . ($correctionRequest->admin_notes ? " - {$correctionRequest->admin_notes}" : ''),
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => "Salary request has been {$validated['status']}.",
            'request' => $correctionRequest->fresh(['user', 'branch', 'reviewer']),
        ]);
    }
}
