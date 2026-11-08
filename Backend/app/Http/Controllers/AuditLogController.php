<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Branch;
use App\Models\Order;
use App\Models\CustomCakeRequest;
use App\Models\StockTransfer;
use Carbon\Carbon;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Security check: Only Super Admin, Main Branch Manager, and Branch Managers/Admins can view audit logs
        // Staff members (Sellers, Cashiers, Servants, Delivery Staff) and Customers are blocked
        if (!$user || !in_array($user->role, ['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Admin'])) {
            return response()->json(['message' => 'Unauthorized. Staff members cannot view audit logs.'], 403);
        }

        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        $isSuperAdmin = ($user->role === 'Super Admin');
        $scopedBranchId = null;

        if (!$isSuperAdmin) {
            // Branch Managers are strictly scoped to their assigned branch
            $scopedBranchId = $user->branch_id ?? Branch::where('manager_id', $user->user_id)->value('branch_id');
            if (!$scopedBranchId && $user->role === 'Main Branch Manager') {
                $scopedBranchId = 1;
            }
        } elseif ($request->filled('branch_id') && $request->branch_id !== 'all') {
            $scopedBranchId = (int) $request->branch_id;
        } elseif ($request->filled('branch') && $request->branch !== 'all') {
            $b = Branch::where('branch_name', $request->branch)->first();
            if ($b) {
                $scopedBranchId = $b->branch_id;
            }
        }

        // Apply strict branch scoping if a branch is scoped (always for Branch Managers, optional filter for Super Admin)
        if ($scopedBranchId) {
            $branch = Branch::find($scopedBranchId);
            $branchName = $branch?->branch_name ?? '';
            $branchUserIds = User::where('branch_id', $scopedBranchId)->pluck('user_id');

            $branchOrderIds = Order::where('branch_id', $scopedBranchId)->pluck('order_id')->map(fn($id) => (string)$id);
            $branchCakeIds = CustomCakeRequest::where('branch_id', $scopedBranchId)->pluck('request_id')->map(fn($id) => (string)$id);
            $branchTransferIds = StockTransfer::where('source_branch_id', $scopedBranchId)
                ->orWhere('destination_branch_id', $scopedBranchId)
                ->pluck('transfer_id')->map(fn($id) => (string)$id);

            $query->where(function ($q) use ($branchUserIds, $branchName, $branchOrderIds, $branchCakeIds, $branchTransferIds) {
                $q->whereIn('user_id', $branchUserIds);

                if ($branchOrderIds->isNotEmpty()) {
                    $q->orWhere(function ($oq) use ($branchOrderIds) {
                        $oq->where('entity_type', 'Order')->whereIn('entity_id', $branchOrderIds);
                    });
                }
                if ($branchCakeIds->isNotEmpty()) {
                    $q->orWhere(function ($cq) use ($branchCakeIds) {
                        $cq->where('entity_type', 'CustomCakeRequest')->whereIn('entity_id', $branchCakeIds);
                    });
                }
                if ($branchTransferIds->isNotEmpty()) {
                    $q->orWhere(function ($tq) use ($branchTransferIds) {
                        $tq->where('entity_type', 'Transfer')->whereIn('entity_id', $branchTransferIds);
                    });
                }
                if (!empty($branchName)) {
                    $q->orWhere('record', 'like', "%{$branchName}%")
                      ->orWhere('new_value', 'like', "%{$branchName}%")
                      ->orWhere('old_value', 'like', "%{$branchName}%");
                }
            });
        }

        // Filter by user_id
        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action') && $request->action !== 'all') {
            $query->where('action', $request->action);
        }

        // Filter by date or date preset
        $dateFilter = $request->query('date_filter');
        if ($dateFilter === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($dateFilter === 'this_week') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($dateFilter === 'this_month') {
            $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        } elseif ($request->filled('from_date') || $request->filled('start_date') || $request->filled('to_date') || $request->filled('end_date')) {
            $from = $request->input('from_date', $request->input('start_date'));
            $to = $request->input('to_date', $request->input('end_date'));
            if ($from && $to) {
                $query->whereBetween('created_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()]);
            } elseif ($from) {
                $query->where('created_at', '>=', Carbon::parse($from)->startOfDay());
            } elseif ($to) {
                $query->where('created_at', '<=', Carbon::parse($to)->endOfDay());
            }
        } elseif ($request->filled('date')) {
            $query->whereDate('created_at', Carbon::parse($request->date));
        }

        // Search across record, user_name, action, new_value, old_value
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('record', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('new_value', 'like', "%{$search}%")
                  ->orWhere('old_value', 'like', "%{$search}%");
            });
        }

        $logs = $query->take(300)->get()->map(function ($log) {
            return [
                'log_id' => $log->log_id,
                'time' => $log->created_at ? $log->created_at->format('H:i') : '—',
                'date' => $log->created_at ? $log->created_at->format('Y-m-d') : '—',
                'user' => $log->user_name ?? ($log->user ? ($log->user->full_name . ' (' . $log->user->role . ')') : 'System'),
                'action' => $log->action,
                'record' => $log->record ?? '—',
                'old_value' => $log->old_value ?? '—',
                'new_value' => $log->new_value ?? '—',
                'entity_type' => $log->entity_type,
                'entity_id' => $log->entity_id,
            ];
        });

        // Scoped user list: Branch Managers only see their branch staff in filter
        if (!$isSuperAdmin && $scopedBranchId) {
            $usersList = User::where('branch_id', $scopedBranchId)->select('user_id', 'full_name', 'role')->get();
        } else {
            $usersList = User::select('user_id', 'full_name', 'role')->get();
        }

        $actionsList = AuditLog::select('action')->distinct()->pluck('action');
        $branchesList = Branch::select('branch_id', 'branch_name', 'branch_code')->get();

        return response()->json([
            'logs' => $logs,
            'users' => $usersList,
            'actions' => $actionsList,
            'branches' => $branchesList,
            'scoped_branch_id' => $scopedBranchId,
            'is_super_admin' => $isSuperAdmin,
        ]);
    }
}
