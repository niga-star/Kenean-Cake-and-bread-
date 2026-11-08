<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\User;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Allowed: Super Admin, Main Branch Manager, Branch Manager, Branch Admin
        if ($user && !in_array($user->role, ['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Admin'])) {
            return response()->json(['message' => 'Unauthorized. Staff members cannot view audit logs.'], 403);
        }

        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        // Scoping for Branch Managers: only records created by their branch users or related to their branch
        if ($user && in_array($user->role, ['Branch Manager', 'Branch Admin'])) {
            $branchUserIds = User::where('branch_id', $user->branch_id)->pluck('user_id');
            $branchName = $user->branch?->branch_name ?? '';

            $query->where(function ($q) use ($branchUserIds, $branchName) {
                $q->whereIn('user_id', $branchUserIds);
                if (!empty($branchName)) {
                    $q->orWhere('record', 'like', "%{$branchName}%")
                      ->orWhere('new_value', 'like', "%{$branchName}%")
                      ->orWhere('old_value', 'like', "%{$branchName}%");
                }
            });
        }

        if ($request->has('user_id') && $request->user_id && $request->user_id !== 'all') {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action') && $request->action && $request->action !== 'all') {
            $query->where('action', $request->action);
        }

        $logs = $query->get()->map(function ($log) {
            return [
                'log_id' => $log->log_id,
                'time' => $log->created_at ? $log->created_at->format('H:i') : '—',
                'date' => $log->created_at ? $log->created_at->format('Y-m-d') : '—',
                'user' => $log->user_name ?? ($log->user ? $log->user->full_name : 'System'),
                'action' => $log->action,
                'record' => $log->record ?? '—',
                'old_value' => $log->old_value ?? '—',
                'new_value' => $log->new_value ?? '—',
            ];
        });

        // Unique filter options
        $usersList = User::select('user_id', 'full_name', 'role')->get();
        $actionsList = AuditLog::select('action')->distinct()->pluck('action');

        return response()->json([
            'logs' => $logs,
            'users' => $usersList,
            'actions' => $actionsList,
        ]);
    }
}
