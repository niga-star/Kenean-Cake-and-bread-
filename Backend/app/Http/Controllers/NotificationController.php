<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\AppSetting;
use App\Models\Branch;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            $tokenString = $request->bearerToken();
            if ($tokenString) {
                $token = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenString);
                $user = $token?->tokenable;
            }
        }

        $category = $request->query('category');
        $query = Notification::with('branch')->latest('created_at');

        if ($user) {
            if ($user->role === 'Customer') {
                $query->where('user_id', $user->user_id);
            } elseif ($user->role === 'Super Admin') {
                // Rule 1: Remove all Order and Custom Order-related notifications from the Super Admin dashboard.
                // These notifications belong exclusively to the relevant Branch Sellers.
                $query->where(function ($q) {
                    $q->whereNotIn('category', ['Orders', 'Custom Cakes'])
                      ->orWhereNull('category');
                })
                ->where(function ($q) {
                    $q->where('link', 'not like', '%/admin/orders%')
                      ->where('link', 'not like', '%/admin/custom-cakes%')
                      ->orWhereNull('link');
                });

                if ($request->filled('branch_id') && $request->branch_id !== 'all') {
                    $query->where(function ($q) use ($request) {
                        $q->where('branch_id', $request->branch_id)->orWhereNull('branch_id');
                    });
                }
            } elseif (in_array($user->role, ['Main Branch Manager', 'Branch Manager', 'Branch Admin'])) {
                // Rule 2: Branch Managers should only receive notifications relevant to branch management responsibilities
                // (e.g. seller reports, stock/inventory alerts, branch transfer milestones).
                // Operational order preparation and custom order notifications belong exclusively to Branch Sellers.
                $query->where(function ($q) {
                    $q->whereNotIn('category', ['Orders', 'Custom Cakes'])
                      ->orWhereNull('category');
                })
                ->where(function ($q) {
                    $q->where('link', 'not like', '%/admin/orders%')
                      ->where('link', 'not like', '%/admin/custom-cakes%')
                      ->orWhereNull('link');
                });

                $scopedBranchId = $user->branch_id ?? Branch::where('manager_id', $user->user_id)->value('branch_id');
                if (!$scopedBranchId && $user->role === 'Main Branch Manager') {
                    $scopedBranchId = 1;
                }

                if ($scopedBranchId) {
                    $query->where(function ($q) use ($scopedBranchId, $user) {
                        $q->where(function ($sub) use ($scopedBranchId) {
                            $sub->where('branch_id', $scopedBranchId)
                                ->whereNull('user_id');
                        })
                        ->orWhere('user_id', $user->user_id);
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                // Rule 3: Each Branch Seller should receive notifications related ONLY to their own branch orders and custom orders.
                $scopedBranchId = $user->branch_id;
                if ($scopedBranchId) {
                    $query->where('branch_id', $scopedBranchId)
                          ->where(function ($q) use ($user) {
                              $q->whereNull('user_id')
                                ->orWhere('user_id', $user->user_id);
                          });
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        }

        if ($category && $category !== 'All') {
            $query->where('category', $category);
        }

        $unreadCount = (clone $query)->where('is_read', false)->count();

        $notifications = $query->take(50)->get()->map(function ($n) {
            return [
                'notification_id' => $n->notification_id,
                'category' => $n->category,
                'title' => $n->title,
                'message' => $n->message,
                'link' => $n->link,
                'branch_id' => $n->branch_id,
                'branch_name' => $n->branch?->branch_name,
                'is_read' => (bool) $n->is_read,
                'time_ago' => $n->created_at ? ($n->created_at->diffForHumans(null, true) . ' ago') : 'Just now',
                'created_at' => $n->created_at ? $n->created_at->toIso8601String() : null,
            ];
        });

        $alertRules = [
            'low_stock_goes_to' => AppSetting::get('low_stock_notification', 'Branch Admin and Main Manager'),
            'unreceived_reminder' => AppSetting::get('unreceived_transfer_reminder', 'After 24 hours'),
            'daily_summary' => AppSetting::get('daily_summary_email', 'Owner, 20:00'),
        ];

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'alert_rules' => $alertRules,
        ]);
    }

    public function markAllRead(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            $tokenString = $request->bearerToken();
            if ($tokenString) {
                $token = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenString);
                $user = $token?->tokenable;
            }
        }

        $query = Notification::where('is_read', false);
        if ($user && $user->role === 'Customer') {
            $query->where('user_id', $user->user_id);
        } elseif ($user && $user->role === 'Super Admin') {
            $query->where(function ($q) {
                $q->whereNotIn('category', ['Orders', 'Custom Cakes'])
                  ->orWhereNull('category');
            })
            ->where(function ($q) {
                $q->where('link', 'not like', '%/admin/orders%')
                  ->where('link', 'not like', '%/admin/custom-cakes%')
                  ->orWhereNull('link');
            });
        } elseif ($user && in_array($user->role, ['Main Branch Manager', 'Branch Manager', 'Branch Admin'])) {
            $query->where(function ($q) {
                $q->whereNotIn('category', ['Orders', 'Custom Cakes'])
                  ->orWhereNull('category');
            })
            ->where(function ($q) {
                $q->where('link', 'not like', '%/admin/orders%')
                  ->where('link', 'not like', '%/admin/custom-cakes%')
                  ->orWhereNull('link');
            });

            $scopedBranchId = $user->branch_id ?? Branch::where('manager_id', $user->user_id)->value('branch_id');
            if (!$scopedBranchId && $user->role === 'Main Branch Manager') {
                $scopedBranchId = 1;
            }
            if ($scopedBranchId) {
                $query->where(function ($q) use ($scopedBranchId, $user) {
                    $q->where(function ($sub) use ($scopedBranchId) {
                        $sub->where('branch_id', $scopedBranchId)->whereNull('user_id');
                    })->orWhere('user_id', $user->user_id);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user && $user->role !== 'Super Admin') {
            $branchId = $user->branch_id;
            if ($branchId) {
                $query->where('branch_id', $branchId)
                      ->where(function ($q) use ($user) {
                          $q->whereNull('user_id')
                            ->orWhere('user_id', $user->user_id);
                      });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $query->update(['is_read' => true]);
        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function markAsRead(Request $request, $id)
    {
        $n = Notification::findOrFail($id);
        $n->is_read = true;
        $n->save();
        return response()->json(['message' => 'Notification marked as read', 'notification' => $n]);
    }

    public function saveAlertRules(Request $request)
    {
        $validated = $request->validate([
            'low_stock_goes_to' => 'required|string',
            'unreceived_reminder' => 'required|string',
            'daily_summary' => 'required|string',
        ]);

        AppSetting::set('low_stock_notification', $validated['low_stock_goes_to'], 'alerts');
        AppSetting::set('unreceived_transfer_reminder', $validated['unreceived_reminder'], 'alerts');
        AppSetting::set('daily_summary_email', $validated['daily_summary'], 'alerts');

        return response()->json(['message' => 'Alert rules updated successfully']);
    }
}
