<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\DeliveryZone;
use App\Models\AuditLog;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::with(['manager', 'deliveryZones', 'users' => function ($q) {
            $q->where('role', '!=', 'Customer');
        }]);

        if ($request->has('active_only') && $request->active_only) {
            $query->where('status', 'Active');
        }

        $branches = $query->orderBy('branch_type', 'asc')->orderBy('branch_name', 'asc')->get()->map(function ($b) {
            $staffCount = $b->users->filter(function ($u) {
                return in_array($u->role, ['Branch Staff', 'Branch Seller', 'Branch Seller / Cashier', 'Cashier', 'Servant', 'Delivery Staff']);
            })->count();

            $managerCount = $b->users->filter(function ($u) {
                return in_array($u->role, ['Branch Manager', 'Branch Admin', 'Main Branch Manager']);
            })->count();

            $b->staff_count = $staffCount;
            $b->manager_count = $managerCount;
            return $b;
        });

        return response()->json($branches);
    }

    public function show($id)
    {
        $branch = Branch::with(['manager', 'deliveryZones', 'inventory.product'])->findOrFail($id);
        return response()->json($branch);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_name' => 'required|string|max:255',
            'branch_code' => 'required|string|unique:branches,branch_code|max:50',
            'branch_type' => 'required|in:Main,Regular',
            'region' => 'required|string',
            'city' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'opening_hours' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,user_id',
        ]);

        if ($validated['branch_type'] === 'Main') {
            $existingMain = Branch::where('branch_type', 'Main')->first();
            if ($existingMain) {
                return response()->json(['message' => 'Only one main branch is allowed.'], 422);
            }
        }

        $branch = Branch::create($validated);

        // Audit log
        AuditLog::create([
            'user_id' => $request->user()?->user_id,
            'user_name' => $request->user()?->full_name ?? 'Super Admin',
            'action' => 'Branch created',
            'record' => $branch->branch_code,
            'entity_type' => 'Branch',
            'entity_id' => (string) $branch->branch_id,
            'old_value' => '—',
            'new_value' => $branch->branch_name,
            'created_at' => now(),
        ]);

        return response()->json($branch->load('deliveryZones'), 201);
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'branch_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string',
            'address' => 'sometimes|string',
            'opening_hours' => 'sometimes|string',
            'status' => 'sometimes|in:Active,Inactive',
            'is_shopping_enabled' => 'sometimes|boolean',
            'manager_id' => 'nullable|exists:users,user_id',
            'max_managers' => 'nullable|integer|min:1',
            'max_staff' => 'nullable|integer|min:1',
        ]);

        if (array_key_exists('is_shopping_enabled', $validated) && $request->user()?->role !== 'Super Admin') {
            return response()->json([
                'message' => 'Unauthorized: Only Super Admin can change branch shopping availability.'
            ], 403);
        }

        // Constraint check: cannot deactivate branch if open orders or transfers in transit
        if (isset($validated['status']) && $validated['status'] === 'Inactive' && $branch->status === 'Active') {
            $hasOpenOrders = $branch->orders()->whereIn('order_status', ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Out for delivery'])->exists();
            $hasActiveTransfers = $branch->transfersReceived()->whereIn('status', ['Dispatched', 'In transit', 'Partially received'])->exists();

            if ($hasOpenOrders || $hasActiveTransfers) {
                return response()->json([
                    'message' => 'Cannot deactivate branch while open orders or active stock transfers exist.'
                ], 422);
            }
        }

        $oldStatus = $branch->status;
        $oldShopping = $branch->is_shopping_enabled ? 'Enabled' : 'Disabled';
        $branch->update($validated);

        $actionNote = "Status: {$branch->status}";
        if (array_key_exists('is_shopping_enabled', $validated)) {
            $newShopping = $branch->is_shopping_enabled ? 'Enabled' : 'Disabled';
            $actionNote .= " | Shopping: {$newShopping}";
        }

        AuditLog::create([
            'user_id' => $request->user()?->user_id,
            'user_name' => $request->user()?->full_name ?? 'Admin',
            'action' => 'Branch updated',
            'record' => $branch->branch_code,
            'entity_type' => 'Branch',
            'entity_id' => (string) $branch->branch_id,
            'old_value' => "Status: {$oldStatus} | Shopping: {$oldShopping}",
            'new_value' => $actionNote,
            'created_at' => now(),
        ]);

        return response()->json($branch->load('deliveryZones'));
    }

    /**
     * Toggle Customer Shopping Access for a Branch (Super Admin Only)
     */
    public function toggleShoppingAccess(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'Super Admin') {
            return response()->json([
                'message' => 'Unauthorized: Only Super Admin can change branch shopping availability.'
            ], 403);
        }

        $branch = Branch::findOrFail($id);
        $newState = $request->has('is_shopping_enabled')
            ? filter_var($request->input('is_shopping_enabled'), FILTER_VALIDATE_BOOLEAN)
            : !$branch->is_shopping_enabled;

        $oldState = $branch->is_shopping_enabled ? 'Enabled' : 'Disabled';
        $branch->is_shopping_enabled = $newState;
        $branch->save();
        $newStateStr = $newState ? 'Enabled' : 'Disabled';

        AuditLog::create([
            'user_id' => $user->user_id,
            'user_name' => $user->full_name . ' (Super Admin)',
            'action' => 'Branch shopping access toggled',
            'record' => $branch->branch_code . ' (' . $branch->branch_name . ')',
            'entity_type' => 'Branch',
            'entity_id' => (string) $branch->branch_id,
            'old_value' => "Shopping {$oldState}",
            'new_value' => "Shopping {$newStateStr}",
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => "Customer shopping access for {$branch->branch_name} has been {$newStateStr}.",
            'branch' => $branch->load('deliveryZones'),
        ]);
    }

    public function deliveryZones($id)
    {
        $branch = Branch::findOrFail($id);
        return response()->json($branch->deliveryZones);
    }

    public function addDeliveryZone(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'zone_name' => 'required|string|max:255',
            'delivery_fee' => 'required|numeric|min:0',
            'status' => 'sometimes|in:Active,Inactive',
        ]);

        $zone = DeliveryZone::create([
            'branch_id' => $branch->branch_id,
            'zone_name' => $validated['zone_name'],
            'delivery_fee' => $validated['delivery_fee'],
            'status' => $validated['status'] ?? 'Active',
        ]);

        AuditLog::create([
            'user_id' => $request->user()?->user_id,
            'user_name' => $request->user()?->full_name ?? 'Super Admin',
            'action' => 'Delivery zone added',
            'record' => $branch->branch_name . ' · ' . $zone->zone_name,
            'entity_type' => 'DeliveryZone',
            'entity_id' => (string) $zone->zone_id,
            'old_value' => '—',
            'new_value' => 'ETB ' . number_format($zone->delivery_fee, 0),
            'created_at' => now(),
        ]);

        return response()->json($zone, 201);
    }

    public function deleteDeliveryZone(Request $request, $id, $zoneId)
    {
        $branch = Branch::findOrFail($id);
        $zone = DeliveryZone::where('branch_id', $branch->branch_id)->where('zone_id', $zoneId)->firstOrFail();
        $zoneName = $zone->zone_name;
        $zone->delete();

        AuditLog::create([
            'user_id' => $request->user()?->user_id,
            'user_name' => $request->user()?->full_name ?? 'Super Admin',
            'action' => 'Delivery zone removed',
            'record' => $branch->branch_name . ' · ' . $zoneName,
            'entity_type' => 'DeliveryZone',
            'entity_id' => (string) $zoneId,
            'old_value' => $zoneName,
            'new_value' => 'Removed',
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'Delivery zone deleted successfully.']);
    }
}
