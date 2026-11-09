<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Branch;
use App\Models\AuditLog;

class UserController extends Controller
{
    /**
     * List users and staff.
     * - Super Admin: views all branches and users.
     * - Main Branch Manager: views Main Branch staff.
     * - Branch Manager: views staff assigned to their own branch only.
     * - Non-managers (Sellers, Servants, Delivery): forbidden (403).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user && in_array($user->role, ['Branch Staff', 'Branch Seller', 'Branch Seller / Cashier', 'Cashier', 'Servant', 'Delivery Staff', 'Customer'])) {
            return response()->json(['message' => 'Staff members are not authorized to view user management.'], 403);
        }

        $role = $request->query('role');
        $branchId = $request->query('branch_id');
        $tab = $request->query('tab', 'staff'); // 'staff' or 'customers'

        $query = User::with('branch');

        // Scoping for Branch Managers: strictly restricted to their own branch
        if ($user && in_array($user->role, ['Branch Manager', 'Branch Admin', 'Main Branch Manager'])) {
            if ($tab === 'customers') {
                return response()->json([]);
            }
            $userBranchId = $user->branch_id ?? Branch::where('manager_id', $user->user_id)->value('branch_id');
            if ($userBranchId) {
                $query->where('branch_id', $userBranchId);
            } else {
                return response()->json([]);
            }
            // Branch manager cannot see Super Admin accounts
            $query->where('role', '!=', 'Super Admin');
        } elseif ($user && $user->role === 'Super Admin') {
            if ($branchId && $branchId !== 'all') {
                $query->where('branch_id', $branchId);
            }
        }

        if ($tab === 'customers') {
            $query->where('role', 'Customer');
        } else {
            $query->where('role', '!=', 'Customer');
        }

        if ($role && $role !== 'all') {
            if ($role === 'Branch Manager') {
                $query->whereIn('role', ['Branch Manager', 'Branch Admin']);
            } elseif ($role === 'Branch Staff') {
                $query->whereIn('role', ['Branch Staff', 'Branch Seller', 'Branch Seller / Cashier', 'Cashier', 'Servant', 'Delivery Staff']);
            } else {
                $query->where('role', $role);
            }
        }

        $isSuperAdmin = ($user && $user->role === 'Super Admin');

        $users = $query->get()->map(function ($u) use ($isSuperAdmin, $user) {
            $initials = '';
            $parts = explode(' ', $u->full_name);
            foreach ($parts as $p) {
                $initials .= strtoupper(substr($p, 0, 1));
            }
            if (strlen($initials) < 2) $initials = strtoupper(substr($u->role, 0, 2));

            // Standardize display role: 'Branch Admin' presented as 'Branch Manager'
            $displayRole = $u->role === 'Branch Admin' ? 'Branch Manager' : $u->role;

            return [
                'user_id' => $u->user_id,
                'initials' => substr($initials, 0, 2),
                'full_name' => $u->full_name,
                'full_name_am' => $u->full_name_am,
                'email' => $u->email,
                'phone' => $u->phone,
                'role' => $displayRole,
                'original_role' => $u->role,
                'branch_id' => $u->branch_id,
                'branch_name' => $u->branch ? $u->branch->branch_name : ($u->role === 'Customer' ? 'Online Customer' : 'All branches'),
                'salary' => ($isSuperAdmin || ($user && $user->user_id === $u->user_id)) ? (float) $u->salary : null,
                'employment_status' => $u->employment_status ?: $u->status,
                'status' => $u->status,
                'hire_date' => $u->hire_date ? $u->hire_date->format('Y-m-d') : null,
                'last_sign_in' => 'Today',
            ];
        });

        return response()->json($users);
    }

    /**
     * Create a user/staff member.
     * - Super Admin can create any role, set salary, and assign to any branch.
     * - Branch Manager can only create staff for their own branch (Sellers, Servants, Delivery), cannot set salary.
     * - Enforces branch staff and manager quotas set by Super Admin.
     */
    public function store(Request $request)
    {
        $currentUser = $request->user();
        if ($currentUser && in_array($currentUser->role, ['Branch Staff', 'Branch Seller', 'Branch Seller / Cashier', 'Cashier', 'Servant', 'Delivery Staff', 'Customer'])) {
            return response()->json(['message' => 'Staff members are not authorized to create users.'], 403);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'full_name_am' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:40',
            'role' => 'required|string',
            'branch_id' => 'nullable|exists:branches,branch_id',
            'salary' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:Active,Inactive,Suspended',
            'employment_status' => 'nullable|in:Active,On Leave,Inactive,Suspended,Terminated',
            'hire_date' => 'nullable|date',
        ]);

        $role = $validated['role'];
        if ($role === 'Branch Admin') {
            $role = 'Branch Manager';
        }

        $branchId = $validated['branch_id'] ?? null;

        // Branch Manager permission enforcement
        if ($currentUser && in_array($currentUser->role, ['Branch Manager', 'Branch Admin', 'Main Branch Manager'])) {
            $allowedStaffRoles = [
                'Branch Seller',
                'Branch Seller / Cashier',
                'Cashier',
                'Servant',
                'Delivery Staff',
                'Branch Staff',
            ];

            if (!in_array($role, $allowedStaffRoles)) {
                return response()->json([
                    'message' => 'Branch Managers can only add branch staff (Sellers, Servants, Delivery Staff).'
                ], 403);
            }

            // Strictly bind to the manager's assigned branch
            $userBranchId = $currentUser->branch_id ?? Branch::where('manager_id', $currentUser->user_id)->value('branch_id');
            $branchId = $userBranchId;
            // Branch managers cannot set salaries
            $validated['salary'] = 0.00;
        }

        // Branch required for branch-scoped roles
        $branchScopedRoles = [
            'Branch Manager',
            'Branch Admin',
            'Main Branch Manager',
            'Branch Seller',
            'Branch Seller / Cashier',
            'Cashier',
            'Servant',
            'Delivery Staff',
            'Branch Staff',
        ];

        if (in_array($role, $branchScopedRoles) && empty($branchId)) {
            return response()->json(['message' => 'Branch staff and managers must have an assigned branch.'], 422);
        }

        if (in_array($role, ['Super Admin', 'Customer'])) {
            $branchId = null;
        }

        // Quota verification for the branch
        if ($branchId) {
            $branch = Branch::find($branchId);
            if ($branch) {
                $isManagerRole = in_array($role, ['Branch Manager', 'Branch Admin', 'Main Branch Manager']);
                if ($isManagerRole) {
                    $currentManagers = User::where('branch_id', $branchId)
                        ->whereIn('role', ['Branch Manager', 'Branch Admin', 'Main Branch Manager'])
                        ->count();
                    $maxManagers = $branch->max_managers ?: 1;
                    if ($currentManagers >= $maxManagers && (!$currentUser || $currentUser->role !== 'Super Admin')) {
                        return response()->json([
                            'message' => "Manager quota reached for {$branch->branch_name} (Maximum: {$maxManagers})."
                        ], 422);
                    }
                } else {
                    $currentStaff = User::where('branch_id', $branchId)
                        ->whereIn('role', ['Branch Staff', 'Branch Seller', 'Branch Seller / Cashier', 'Cashier', 'Servant', 'Delivery Staff'])
                        ->count();
                    $maxStaff = $branch->max_staff ?: 15;
                    if ($currentStaff >= $maxStaff && (!$currentUser || $currentUser->role !== 'Super Admin')) {
                        return response()->json([
                            'message' => "Branch staff limit reached for {$branch->branch_name} (Maximum allowed: {$maxStaff}). Please request a quota increase from the Super Admin."
                        ], 422);
                    }
                }
            }
        }

        // Only Super Admin can set salary upon creation
        $salary = 0.00;
        if ($currentUser && $currentUser->role === 'Super Admin' && isset($validated['salary'])) {
            $salary = (float) $validated['salary'];
        }

        $user = User::create([
            'full_name' => $validated['full_name'],
            'full_name_am' => $validated['full_name_am'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $role,
            'branch_id' => $branchId,
            'salary' => $salary,
            'employment_status' => $validated['employment_status'] ?? ($validated['status'] ?? 'Active'),
            'status' => $validated['status'] ?? 'Active',
            'hire_date' => $validated['hire_date'] ?? now()->toDateString(),
            'password' => Hash::make('password123'),
        ]);

        // When Super Admin creates a Branch Manager, keep branch.manager_id synchronized
        if ($currentUser && $currentUser->role === 'Super Admin' && in_array($role, ['Branch Manager', 'Main Branch Manager']) && $branchId) {
            Branch::where('branch_id', $branchId)->update(['manager_id' => $user->user_id]);
        }

        AuditLog::create([
            'user_id' => $currentUser?->user_id,
            'user_name' => $currentUser?->full_name ?? 'Super Admin',
            'action' => 'User created',
            'record' => $user->full_name,
            'entity_type' => 'User',
            'entity_id' => (string) $user->user_id,
            'old_value' => '—',
            'new_value' => "{$user->role} · " . ($user->branch ? $user->branch->branch_name : 'All branches') . ($user->salary > 0 ? " · ETB {$user->salary}" : ""),
            'created_at' => now(),
        ]);

        return response()->json($user->load('branch'), 201);
    }

    /**
     * Update an existing user or staff member.
     * - Super Admin can update all fields (role, branch, salary, etc.).
     * - Branch Manager can only update staff in their own branch, cannot edit salary or reassign branch.
     */
    public function update(Request $request, $id)
    {
        $currentUser = $request->user();
        if (!$currentUser) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (in_array($currentUser->role, ['Branch Staff', 'Branch Seller', 'Branch Seller / Cashier', 'Cashier', 'Servant', 'Delivery Staff', 'Customer'])) {
            return response()->json(['message' => 'Staff members are not authorized to update users.'], 403);
        }

        $user = User::findOrFail($id);
        $isSuperAdmin = ($currentUser->role === 'Super Admin');
        $managerRoles = ['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Admin'];
        $targetIsManager = in_array($user->role, $managerRoles);

        // Security & RBAC Enforcement:
        // 1. Branch Manager Account: Branch Managers must not be able to change their own account status (Active, Inactive, Suspended).
        // 2. Branch Staff Management: Branch Managers can activate, deactivate, or restrict staff members belonging to their own branch only.
        // 3. Security: Prevent Branch Managers from changing their own status through frontend, API, or direct requests.
        // 4. Branch Isolation: Branch Managers must never be able to modify staff accounts belonging to other branches.
        if (!$isSuperAdmin) {
            // Cannot modify own account status or details through management API
            if ($user->user_id === $currentUser->user_id) {
                return response()->json([
                    'message' => 'Access denied: Branch Managers cannot change their own account status. Only the Super Admin can manage and change a Branch Manager account status.'
                ], 403);
            }

            // Cannot modify any other manager or super admin account
            if ($targetIsManager) {
                return response()->json([
                    'message' => 'Access denied: Only the Super Admin can manage Branch Manager accounts and account statuses.'
                ], 403);
            }

            // Strictly restricted to staff belonging to the manager's assigned branch
            $userBranchId = $currentUser->branch_id ?? Branch::where('manager_id', $currentUser->user_id)->value('branch_id');
            if (!$userBranchId || (int) $user->branch_id !== (int) $userBranchId) {
                return response()->json([
                    'message' => 'Access denied: You can only manage staff belonging to your assigned branch.'
                ], 403);
            }

            if ($user->role === 'Customer') {
                return response()->json([
                    'message' => 'Access denied: Branch Managers cannot modify customer accounts.'
                ], 403);
            }
        }

        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'full_name_am' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:40',
            'role' => 'sometimes|string',
            'branch_id' => 'nullable|exists:branches,branch_id',
            'salary' => 'nullable|numeric|min:0',
            'employment_status' => 'sometimes|in:Active,On Leave,Inactive,Suspended,Terminated',
            'status' => 'sometimes|in:Active,Inactive,Suspended',
            'hire_date' => 'nullable|date',
        ]);

        // Branch Managers CANNOT edit salary, change branch, or assign manager roles
        if (!$isSuperAdmin) {
            unset($validated['salary']);
            unset($validated['branch_id']);
            if (isset($validated['role'])) {
                $allowedStaffRoles = [
                    'Branch Seller',
                    'Branch Seller / Cashier',
                    'Cashier',
                    'Servant',
                    'Delivery Staff',
                    'Branch Staff',
                ];
                if (!in_array($validated['role'], $allowedStaffRoles)) {
                    return response()->json([
                        'message' => 'Access denied: Branch Managers can only assign branch staff roles (Sellers, Servants, Delivery Staff).'
                    ], 403);
                }
            }
        }

        if (isset($validated['role'])) {
            if ($validated['role'] === 'Branch Admin') {
                $validated['role'] = 'Branch Manager';
            }
            if (in_array($validated['role'], ['Super Admin', 'Customer'])) {
                $validated['branch_id'] = null;
            }
        }

        // Synchronize status and employment_status seamlessly
        if (isset($validated['status']) && !isset($validated['employment_status'])) {
            if ($validated['status'] === 'Suspended') {
                $validated['employment_status'] = 'Suspended';
            } elseif ($validated['status'] === 'Inactive') {
                $validated['employment_status'] = 'Inactive';
            } elseif ($validated['status'] === 'Active') {
                $validated['employment_status'] = 'Active';
            }
        } elseif (isset($validated['employment_status']) && !isset($validated['status'])) {
            if (in_array($validated['employment_status'], ['Inactive', 'Suspended', 'Terminated'])) {
                $validated['status'] = $validated['employment_status'] === 'Suspended' ? 'Suspended' : 'Inactive';
            } else {
                $validated['status'] = 'Active';
            }
        }

        $oldStatus = $user->status;
        $oldVal = "{$user->role} · " . ($user->branch ? $user->branch->branch_name : 'All branches') . " · Salary: ETB {$user->salary} · {$user->status}";

        $oldSalary = (float) $user->salary;
        $user->update($validated);
        $user->load('branch');

        // Immediately revoke active sessions if account is deactivated or suspended
        if (isset($validated['status']) && in_array($validated['status'], ['Inactive', 'Suspended'])) {
            $user->tokens()->delete();
        }

        $newVal = "{$user->role} · " . ($user->branch ? $user->branch->branch_name : 'All branches') . " · Salary: ETB {$user->salary} · {$user->status}";

        // Audit log for status modification or user update
        $action = 'User updated';
        if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
            $action = $targetIsManager 
                ? "Branch Manager account status updated ({$oldStatus} -> {$validated['status']})"
                : "Staff account status updated ({$oldStatus} -> {$validated['status']})";
        }

        AuditLog::create([
            'user_id' => $currentUser->user_id,
            'user_name' => $currentUser->full_name . ' (' . $currentUser->role . ')',
            'action' => $action,
            'record' => $user->full_name . ($user->branch ? " · {$user->branch->branch_name}" : ""),
            'entity_type' => 'User',
            'entity_id' => (string) $user->user_id,
            'old_value' => $oldVal,
            'new_value' => $newVal,
            'created_at' => now(),
        ]);

        // Log salary change separately if modified by Super Admin
        if (isset($validated['salary']) && (float) $validated['salary'] !== $oldSalary) {
            AuditLog::create([
                'user_id' => $currentUser->user_id,
                'user_name' => $currentUser->full_name . ' (' . $currentUser->role . ')',
                'action' => 'Salary updated',
                'record' => $user->full_name,
                'entity_type' => 'Salary',
                'entity_id' => (string) $user->user_id,
                'old_value' => "ETB " . number_format($oldSalary, 2),
                'new_value' => "ETB " . number_format((float) $validated['salary'], 2),
                'created_at' => now(),
            ]);
        }

        return response()->json($user);
    }

    /**
     * Get branch staff and manager quota usage.
     */
    public function branchQuotas(Request $request)
    {
        $currentUser = $request->user();
        if (!$currentUser || in_array($currentUser->role, ['Customer', 'Branch Staff', 'Branch Seller', 'Servant', 'Delivery Staff'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $query = Branch::with(['users' => function ($q) {
            $q->where('role', '!=', 'Customer');
        }]);

        if (in_array($currentUser->role, ['Branch Manager', 'Branch Admin'])) {
            $query->where('branch_id', $currentUser->branch_id);
        }

        $branches = $query->get()->map(function ($b) {
            $managerCount = $b->users->filter(function ($u) {
                return in_array($u->role, ['Branch Manager', 'Branch Admin', 'Main Branch Manager']);
            })->count();

            $staffCount = $b->users->filter(function ($u) {
                return in_array($u->role, ['Branch Staff', 'Branch Seller', 'Branch Seller / Cashier', 'Cashier', 'Servant', 'Delivery Staff']);
            })->count();

            return [
                'branch_id' => $b->branch_id,
                'branch_name' => $b->branch_name,
                'branch_code' => $b->branch_code,
                'manager_count' => $managerCount,
                'max_managers' => $b->max_managers ?: 1,
                'staff_count' => $staffCount,
                'max_staff' => $b->max_staff ?: 15,
                'is_staff_quota_reached' => $staffCount >= ($b->max_staff ?: 15),
                'is_manager_quota_reached' => $managerCount >= ($b->max_managers ?: 1),
            ];
        });

        return response()->json($branches);
    }

    /**
     * Delete an employee account.
     * - Super Admin can delete any account (except themselves).
     * - Branch Managers can delete staff within their own branch.
     */
    public function destroy(Request $request, $id)
    {
        $currentUser = $request->user();
        if (!$currentUser) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $targetUser = User::findOrFail($id);
        if ($targetUser->user_id === $currentUser->user_id) {
            return response()->json(['message' => 'Cannot delete your own account.'], 422);
        }

        $isSuperAdmin = ($currentUser->role === 'Super Admin');
        $isBranchManager = in_array($currentUser->role, ['Branch Manager', 'Main Branch Manager', 'Branch Admin']);

        if (!$isSuperAdmin && !$isBranchManager) {
            return response()->json(['message' => 'Unauthorized: Only Super Admin and Branch Managers can remove staff accounts.'], 403);
        }

        $userBranchId = $currentUser->branch_id ?? Branch::where('manager_id', $currentUser->user_id)->value('branch_id');

        if ($isBranchManager) {
            if (!$userBranchId || (int) $targetUser->branch_id !== (int) $userBranchId) {
                return response()->json(['message' => 'Access denied: You can only delete staff from your own assigned branch.'], 403);
            }
            if (in_array($targetUser->role, ['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Admin'])) {
                return response()->json(['message' => 'Access denied: Only the Super Admin can manage or remove Branch Manager accounts.'], 403);
            }
        }

        $userName = $targetUser->full_name;

        // If deleting a manager, unlink branch.manager_id
        Branch::where('manager_id', $targetUser->user_id)->update(['manager_id' => null]);

        $targetUser->delete();

        AuditLog::create([
            'user_id' => $currentUser->user_id,
            'user_name' => $currentUser->full_name,
            'action' => 'User deleted',
            'record' => $userName,
            'entity_type' => 'User',
            'entity_id' => (string) $id,
            'old_value' => $userName,
            'new_value' => 'Deleted',
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'User deleted successfully.']);
    }
}
