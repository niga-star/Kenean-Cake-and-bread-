<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomCakeRequest;
use App\Models\CustomCakeImage;
use App\Models\Branch;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Notification;
use Carbon\Carbon;

class CustomCakeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'requests' => [],
                'summary' => [
                    'submitted' => 0,
                    'quoted' => 0,
                    'confirmed' => 0,
                    'ready_this_week' => 0,
                ]
            ]);
        }

        $userBranchId = $user?->branch_id ?? ($user ? Branch::where('manager_id', $user->user_id)->value('branch_id') : null);
        $query = CustomCakeRequest::with(['branch', 'user', 'images'])->latest('created_at');

        // Scoping rule: Super Admin does not manage custom cake orders; these belong exclusively to Branch Sellers and Branch Managers.
        if ($user && $user->role !== 'Customer') {
            if ($user->role === 'Super Admin') {
                return response()->json([
                    'requests' => [],
                    'summary' => [
                        'submitted' => 0,
                        'quoted' => 0,
                        'confirmed' => 0,
                        'ready_this_week' => 0,
                    ],
                    'message' => 'Custom cake management belongs exclusively to Branch Sellers and Branch Managers.',
                ]);
            } elseif ($userBranchId) {
                $query->where('branch_id', $userBranchId);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($user && $user->role === 'Customer') {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->user_id);
                if (!empty($user->phone)) {
                    $q->orWhere('customer_phone', $user->phone);
                }
            });
        }

        if ($request->has('status') && $request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('flavor', 'like', "%{$search}%");
            });
        }

        // Date filter (Step 6: Today, This Week, This Month, Custom Date Range, All Orders)
        $dateFilter = $request->query('date_range') ?: $request->query('date');
        if ($dateFilter && $dateFilter !== 'all') {
            $now = Carbon::now();
            if ($dateFilter === 'today') {
                $query->whereDate('created_at', Carbon::today());
            } elseif ($dateFilter === 'this_week' || $dateFilter === 'week') {
                $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
            } elseif ($dateFilter === 'this_month' || $dateFilter === 'month') {
                $query->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
            } elseif ($dateFilter === 'custom') {
                $startDate = $request->query('start_date');
                $endDate = $request->query('end_date') ?: $startDate;
                if ($startDate) {
                    $query->whereDate('created_at', '>=', $startDate)
                          ->whereDate('created_at', '<=', $endDate);
                }
            }
        } elseif ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
        }

        $requests = $query->get();

        // Calculate card metrics respecting branch scoping
        $allForMetrics = CustomCakeRequest::query();
        if ($user && $user->role !== 'Super Admin' && $userBranchId) {
            $allForMetrics->where('branch_id', $userBranchId);
        }

        $submittedCount = (clone $allForMetrics)->where('status', 'Submitted')->count();
        $quotedCount = (clone $allForMetrics)->where('status', 'Quoted')->count();
        $confirmedCount = (clone $allForMetrics)->where('status', 'Confirmed')->count();
        $readyThisWeekCount = (clone $allForMetrics)->where('status', 'Ready')->count();

        return response()->json([
            'requests' => $requests,
            'summary' => [
                'submitted' => $submittedCount,
                'quoted' => $quotedCount,
                'confirmed' => $confirmedCount,
                'ready_this_week' => $readyThisWeekCount,
            ]
        ]);
    }

    public function show(Request $request, $id)
    {
        $cakeRequest = CustomCakeRequest::with(['branch', 'user', 'images'])
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('request_id', $id)->orWhere('request_number', $id);
                } else {
                    $q->where('request_number', $id);
                }
            })
            ->firstOrFail();

        $user = $request->user();
        if ($user && $user->role === 'Super Admin') {
            return response()->json(['message' => 'Custom cake management belongs exclusively to Branch Sellers and Branch Managers.'], 403);
        }
        $userBranchId = $user?->branch_id ?? ($user ? Branch::where('manager_id', $user->user_id)->value('branch_id') : null);
        if ($user && $user->role !== 'Customer' && (!$userBranchId || $cakeRequest->branch_id != $userBranchId)) {
            return response()->json(['message' => 'Access denied: You can only view custom cake requests for your assigned branch.'], 403);
        }
        if ($user && $user->role === 'Customer' && $cakeRequest->user_id && $cakeRequest->user_id != $user->user_id) {
            if (!$user->phone || $cakeRequest->customer_phone !== $user->phone) {
                return response()->json(['message' => 'Access denied: You can only view your own custom cake requests.'], 403);
            }
        }
        return response()->json($cakeRequest);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'message' => 'Authentication required: Please sign in or create an account to submit a custom cake order.'
            ], 401);
        }

        // Harmonize field aliases from frontend
        if (!$request->has('cake_size') && $request->has('size')) {
            $request->merge(['cake_size' => $request->size]);
        }
        if (!$request->has('cake_size') && $request->has('servings')) {
            $request->merge(['cake_size' => $request->servings]);
        }
        if (!$request->has('needed_by') && $request->has('needed_by_date')) {
            $request->merge(['needed_by' => $request->needed_by_date]);
        }
        if (!$request->has('needed_by') && $request->has('needed_date')) {
            $request->merge(['needed_by' => $request->needed_date]);
        }
        if (!$request->has('design') && $request->has('description')) {
            $desc = $request->description;
            if ($request->has('color_scheme')) {
                $desc .= ' [Colors: ' . $request->color_scheme . ']';
            }
            $request->merge(['design' => $desc]);
        }
        if (!$request->has('message') && $request->has('message_on_cake')) {
            $request->merge(['message' => $request->message_on_cake]);
        }

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,branch_id',
            'customer_name' => 'nullable|string|max:150',
            'customer_phone' => 'nullable|string|max:40',
            'cake_size' => 'required|string',
            'flavor' => 'required|string',
            'design' => 'nullable|string',
            'design_config' => 'nullable',
            'snapshot_url' => 'nullable|string',
            'message' => 'nullable|string',
            'needed_by' => 'required|date|after_or_equal:today',
            'image_urls' => 'nullable|array',
            'image_urls.*' => 'nullable|string',
        ]);

        $branch = Branch::findOrFail($validated['branch_id']);
        if ($branch->status !== 'Active' || !$branch->is_shopping_enabled) {
            return response()->json([
                'message' => 'Sorry, this branch is currently unavailable for shopping.'
            ], 422);
        }

        $customerName = trim($request->input('customer_name') ?? $request->input('name') ?? '');
        $customerPhone = trim($request->input('customer_phone') ?? $request->input('phone') ?? '');

        $cakeUserId = null;
        if ($user && $user->role === 'Customer') {
            $cakeUserId = $user->user_id;
            // Registered customer's full name is authoritative; discard stale demo names like 'Abebe Kebede'
            $customerName = (!empty($customerName) && strcasecmp($customerName, 'Abebe Kebede') !== 0) ? $customerName : ($user->full_name ?: 'Customer');
            $customerPhone = (!empty($customerPhone) && !str_contains($customerPhone, '100 0001')) ? $customerPhone : ($user->phone ?: null);
        } elseif (!empty($customerPhone)) {
            $existingCustomer = \App\Models\User::where('phone', $customerPhone)->first();
            if ($existingCustomer) {
                $cakeUserId = $existingCustomer->user_id;
                $customerName = (!empty($customerName) && strcasecmp($customerName, 'Abebe Kebede') !== 0) ? $customerName : $existingCustomer->full_name;
            } else {
                $realCustomer = \App\Models\User::create([
                    'full_name' => (!empty($customerName) && strcasecmp($customerName, 'Abebe Kebede') !== 0) ? $customerName : 'Customer',
                    'email' => 'cake_cust_' . time() . '_' . mt_rand(100, 999) . '@sweetbakes.local',
                    'phone' => $customerPhone,
                    'password' => \Illuminate\Support\Facades\Hash::make(bin2hex(random_bytes(16))),
                    'role' => 'Customer',
                    'status' => 'Active',
                ]);
                $cakeUserId = $realCustomer->user_id;
            }
        } elseif ($user) {
            $cakeUserId = $user->user_id;
            $customerName = (!empty($customerName) && strcasecmp($customerName, 'Abebe Kebede') !== 0) ? $customerName : ($user->full_name ?: 'Customer');
            $customerPhone = (!empty($customerPhone) && !str_contains($customerPhone, '100 0001')) ? $customerPhone : ($user->phone ?: null);
        } else {
            $guestCustomer = \App\Models\User::create([
                'full_name' => (!empty($customerName) && strcasecmp($customerName, 'Abebe Kebede') !== 0) ? $customerName : 'Guest Customer',
                'email' => 'cake_guest_' . time() . '_' . mt_rand(100, 999) . '@sweetbakes.local',
                'phone' => null,
                'password' => \Illuminate\Support\Facades\Hash::make(bin2hex(random_bytes(16))),
                'role' => 'Customer',
                'status' => 'Active',
            ]);
            $cakeUserId = $guestCustomer->user_id;
        }

        if (empty($customerName)) {
            $customerName = 'Customer';
        }

        $rand = mt_rand(100, 999);
        $reqNum = "CC-0{$rand}";
        while (CustomCakeRequest::where('request_number', $reqNum)->exists()) {
            $reqNum = "CC-0" . mt_rand(100, 999);
        }

        $cakeReq = CustomCakeRequest::create([
            'request_number' => $reqNum,
            'user_id' => $cakeUserId,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone ?: null,
            'branch_id' => $validated['branch_id'],
            'cake_size' => $validated['cake_size'],
            'flavor' => $validated['flavor'],
            'design' => $validated['design'] ?? null,
            'design_config' => $request->input('design_config'),
            'snapshot_url' => $this->saveBase64Image($request->input('snapshot_url')),
            'message' => $validated['message'] ?? null,
            'needed_by' => $validated['needed_by'],
            'deposit_required_percentage' => 50.00,
            'status' => 'Submitted',
        ]);

        if (!empty($validated['image_urls'])) {
            foreach ($validated['image_urls'] as $url) {
                if ($url) {
                    $savedUrl = $this->saveBase64Image($url, 'custom_cakes/references');
                    CustomCakeImage::create([
                        'request_id' => $cakeReq->request_id,
                        'image_url' => $savedUrl,
                    ]);
                }
            }
        }

        $cakeReq->loadMissing('branch');
        AuditLog::create([
            'user_id' => $user?->user_id,
            'user_name' => $user ? ($user->full_name . ' (' . $user->role . ')') : 'Customer',
            'action' => 'Custom cake submitted',
            'record' => $cakeReq->request_number . ($cakeReq->branch ? " · {$cakeReq->branch->branch_name}" : ""),
            'entity_type' => 'CustomCakeRequest',
            'entity_id' => (string) $cakeReq->request_id,
            'old_value' => '—',
            'new_value' => 'Submitted',
            'created_at' => now(),
        ]);

        // Real-time branch staff notification for new custom cake (Protected against duplicate)
        $alreadyStaffNotified = Notification::where('branch_id', $cakeReq->branch_id)
            ->where('category', 'Orders')
            ->where('title', 'like', "%{$cakeReq->request_number}%")
            ->exists();

        if (!$alreadyStaffNotified) {
            Notification::create([
                'user_id' => null,
                'branch_id' => $cakeReq->branch_id,
                'category' => 'Orders',
                'title' => "Custom Cake Request: {$customerName}",
                'message' => "New custom cake request {$cakeReq->request_number} from {$customerName} is waiting for an official quotation at {$cakeReq->branch?->branch_name}.",
                'link' => '/admin/custom-cakes',
            ]);
        }

        if ($cakeUserId) {
            $alreadyCustomerNotified = Notification::where('user_id', $cakeUserId)
                ->where('category', 'Orders')
                ->where('title', 'like', "%{$cakeReq->request_number}%")
                ->exists();

            if (!$alreadyCustomerNotified) {
                Notification::create([
                    'user_id' => $cakeUserId,
                    'branch_id' => $cakeReq->branch_id,
                    'category' => 'Orders',
                    'title' => 'Custom Cake Request Received',
                    'message' => "Your custom cake request {$cakeReq->request_number} has been received. Our bakers will prepare an official quotation shortly.",
                    'link' => '/account?tab=custom-cakes',
                ]);
            }
        }

        return response()->json($cakeReq->load(['branch', 'images']), 201);
    }

    public function quote(Request $request, $id)
    {
        $cakeReq = CustomCakeRequest::findOrFail($id);
        $user = $request->user();

        // Retail quoting authorization: Branch Sellers / Cashiers and Managers for their branch
        if ($user && $user->role === 'Super Admin') {
            return response()->json(['message' => 'Super Admin does not manage custom cakes. Custom cake quotation belongs exclusively to Branch Sellers and Branch Managers.'], 403);
        }

        $allowedQuoteRoles = ['Main Branch Manager', 'Branch Manager', 'Branch Admin', 'Branch Seller', 'Branch Seller / Cashier', 'Cashier', 'Branch Staff'];
        if ($user && !in_array($user->role, $allowedQuoteRoles)) {
            return response()->json(['message' => 'Access denied: You are not authorized to quote custom cakes.'], 403);
        }

        $userBranchId = $user?->branch_id ?? ($user ? Branch::where('manager_id', $user->user_id)->value('branch_id') : null);
        if (!$userBranchId || $userBranchId != $cakeReq->branch_id) {
            return response()->json(['message' => 'Access denied: You can only quote custom cakes for your assigned branch.'], 403);
        }

        if (!$request->has('quoted_price') && $request->has('estimated_price')) {
            $request->merge(['quoted_price' => $request->estimated_price]);
        }
        if (!$request->has('quoted_price') && $request->has('price')) {
            $request->merge(['quoted_price' => $request->price]);
        }
        if (!$request->has('deposit_required_percentage') && $request->has('deposit_percentage')) {
            $request->merge(['deposit_required_percentage' => $request->deposit_percentage]);
        }
        if (!$request->has('deposit_required_percentage')) {
            $request->merge(['deposit_required_percentage' => 50]);
        }

        $validated = $request->validate([
            'quoted_price' => 'required|numeric|min:1',
            'deposit_required_percentage' => 'required|numeric|min:0|max:100',
            'quote_notes' => 'nullable|string|max:1000',
            'base_sponge_price' => 'nullable|numeric|min:0',
            'artistry_price' => 'nullable|numeric|min:0',
            'topper_price' => 'nullable|numeric|min:0',
            'valid_until' => 'nullable|string',
        ]);

        $designConfig = is_array($cakeReq->design_config) 
            ? $cakeReq->design_config 
            : (json_decode($cakeReq->design_config, true) ?? []);

        $depositAmount = round((float) ($validated['quoted_price'] * ($validated['deposit_required_percentage'] / 100)), 2);
        $remainingBalance = round((float) ($validated['quoted_price'] - $depositAmount), 2);

        $designConfig['official_quote'] = [
            'quote_number' => 'QT-' . $cakeReq->request_number,
            'quoted_at' => now()->toIso8601String(),
            'quoted_by' => $user ? ($user->full_name . ' (' . $user->role . ')') : 'Branch Seller',
            'total_price' => (float) $validated['quoted_price'],
            'deposit_percentage' => (float) $validated['deposit_required_percentage'],
            'deposit_amount' => $depositAmount,
            'remaining_balance' => $remainingBalance,
            'base_sponge_price' => (float) ($validated['base_sponge_price'] ?? ($validated['quoted_price'] * 0.6)),
            'artistry_price' => (float) ($validated['artistry_price'] ?? ($validated['quoted_price'] * 0.3)),
            'topper_price' => (float) ($validated['topper_price'] ?? ($validated['quoted_price'] * 0.1)),
            'quote_notes' => $validated['quote_notes'] ?? 'Artisan baking according to custom specification. Fresh delivery/pickup guaranteed.',
            'valid_until' => $validated['valid_until'] ?? now()->addDays(7)->format('M d, Y'),
        ];

        $cakeReq->design_config = $designConfig;
        $cakeReq->quoted_price = $validated['quoted_price'];
        $cakeReq->deposit_required_percentage = $validated['deposit_required_percentage'];
        $cakeReq->status = 'Quoted';
        $cakeReq->save();

        $cakeReq->loadMissing(['branch', 'user']);
        AuditLog::create([
            'user_id' => $request->user()?->user_id,
            'user_name' => $request->user()?->full_name ?? 'Branch Seller',
            'action' => 'Custom cake quoted',
            'record' => $cakeReq->request_number . ($cakeReq->branch ? " · {$cakeReq->branch->branch_name}" : ""),
            'entity_type' => 'CustomCakeRequest',
            'entity_id' => (string) $cakeReq->request_id,
            'old_value' => 'Submitted',
            'new_value' => 'Quoted (ETB ' . number_format($validated['quoted_price'], 0) . ')',
            'created_at' => now(),
        ]);

        // Real-time branch staff notification (Duplicate protected)
        $alreadyQuoteNotified = Notification::where('branch_id', $cakeReq->branch_id)
            ->where('category', 'Orders')
            ->where('title', 'like', "%{$cakeReq->request_number}%")
            ->where('title', 'like', '%Official Quote Sent%')
            ->exists();

        if (!$alreadyQuoteNotified) {
            Notification::create([
                'user_id' => null,
                'branch_id' => $cakeReq->branch_id,
                'category' => 'Orders',
                'title' => "Official Quote Sent: {$cakeReq->request_number}",
                'message' => "Official quote of ETB " . number_format($validated['quoted_price'], 0) . " sent to {$cakeReq->customer_name} for {$cakeReq->request_number}.",
                'link' => '/admin/custom-cakes',
            ]);
        }

        // Customer notification
        if ($cakeReq->user_id) {
            $alreadyCustQuote = Notification::where('user_id', $cakeReq->user_id)
                ->where('category', 'Orders')
                ->where('title', 'like', "%{$cakeReq->request_number}%")
                ->where('title', 'like', '%Official Quotation%')
                ->exists();

            if (!$alreadyCustQuote) {
                Notification::create([
                    'user_id' => $cakeReq->user_id,
                    'branch_id' => $cakeReq->branch_id,
                    'category' => 'Orders',
                    'title' => "Official Quotation: {$cakeReq->request_number}",
                    'message' => "{$cakeReq->branch?->branch_name} has prepared an official quote of ETB " . number_format($validated['quoted_price'], 0) . " for your cake. Review details and confirm.",
                    'link' => '/account?tab=custom-cakes',
                ]);
            }
        }

        return response()->json($cakeReq);
    }

    public function updateStatus(Request $request, $id)
    {
        $cakeReq = CustomCakeRequest::findOrFail($id);
        $user = $request->user();

        // Custom cake status management belongs exclusively to Branch Sellers and Branch Managers
        if ($user && $user->role === 'Super Admin') {
            return response()->json([
                'message' => 'Super Admin does not manage custom cakes. Custom cake orders belong exclusively to Branch Sellers and Branch Managers.',
            ], 403);
        }

        // Branch scoping: Branch Managers and staff can only manage their assigned branch
        $userBranchId = $user?->branch_id ?? ($user ? Branch::where('manager_id', $user->user_id)->value('branch_id') : null);
        if (!$userBranchId || $cakeReq->branch_id != $userBranchId) {
            return response()->json(['message' => 'Access denied: You can only manage custom cakes for your assigned branch.'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:Confirmed,Preparing,Ready,Picked up,Delivered,Rejected,Cancelled',
            'rejection_reason' => 'nullable|string',
        ]);

        $oldStatus = $cakeReq->status;
        $cakeReq->status = $validated['status'];
        if (isset($validated['rejection_reason'])) {
            $cakeReq->rejection_reason = $validated['rejection_reason'];
        }
        $cakeReq->save();

        $cakeReq->loadMissing(['branch', 'user']);
        AuditLog::create([
            'user_id' => $request->user()?->user_id,
            'user_name' => $request->user()?->full_name ?? 'Branch Staff',
            'action' => 'Custom cake status updated',
            'record' => $cakeReq->request_number . ($cakeReq->branch ? " · {$cakeReq->branch->branch_name}" : ""),
            'entity_type' => 'CustomCakeRequest',
            'entity_id' => (string) $cakeReq->request_id,
            'old_value' => $oldStatus,
            'new_value' => $validated['status'],
            'created_at' => now(),
        ]);

        // Branch Seller / Staff Notifications (Strictly important custom cake milestones, sent only once per status change)
        $importantCakeStatuses = ['Preparing', 'Ready', 'Delivered', 'Picked up'];
        if (in_array($validated['status'], $importantCakeStatuses) && $oldStatus !== $validated['status']) {
            $alreadySentStaff = Notification::where('branch_id', $cakeReq->branch_id)
                ->where('category', 'Orders')
                ->where('title', 'like', "%{$cakeReq->request_number}%")
                ->where('title', 'like', "%{$validated['status']}%")
                ->exists();

            if (!$alreadySentStaff) {
                $staffTitle = match ($validated['status']) {
                    'Preparing' => "Cake Baking Started: {$cakeReq->request_number}",
                    'Ready' => "Cake Ready for Pickup: {$cakeReq->request_number}",
                    'Delivered', 'Picked up' => "Cake Handed Over: {$cakeReq->request_number}",
                    default => "Custom Cake {$cakeReq->request_number}: {$validated['status']}",
                };

                $staffMsg = match ($validated['status']) {
                    'Preparing' => "Kitchen has started crafting custom cake for {$cakeReq->customer_name}.",
                    'Ready' => "Custom cake for {$cakeReq->customer_name} is finished, chilled, and ready for pickup.",
                    'Delivered', 'Picked up' => "Custom cake for {$cakeReq->customer_name} has been successfully collected/delivered.",
                    default => "Custom cake status updated to {$validated['status']}.",
                };

                Notification::create([
                    'user_id' => null,
                    'branch_id' => $cakeReq->branch_id,
                    'category' => 'Orders',
                    'title' => $staffTitle,
                    'message' => $staffMsg,
                    'link' => '/admin/custom-cakes',
                ]);
            }
        }

        // Customer notification (Only sent once per status change)
        if ($cakeReq->user_id && $oldStatus !== $validated['status']) {
            $alreadySentCust = Notification::where('user_id', $cakeReq->user_id)
                ->where('title', 'like', "%{$cakeReq->request_number}%")
                ->where('title', 'like', "%{$validated['status']}%")
                ->exists();

            if (!$alreadySentCust) {
                Notification::create([
                    'user_id' => $cakeReq->user_id,
                    'branch_id' => $cakeReq->branch_id,
                    'category' => 'Orders',
                    'title' => "Custom Cake: {$validated['status']}",
                    'message' => "Your custom cake {$cakeReq->request_number} is now {$validated['status']}.",
                    'link' => '/account?tab=custom-cakes',
                ]);
            }
        }

        return response()->json($cakeReq);
    }

    public function acceptQuote(Request $request, $id)
    {
        $cakeReq = CustomCakeRequest::findOrFail($id);
        $user = $request->user();

        if ($user && $cakeReq->user_id && $cakeReq->user_id != $user->user_id && !in_array($user->role, ['Super Admin', 'Main Branch Manager'])) {
            return response()->json(['message' => 'Unauthorized: You can only accept quotes for your own custom cake orders.'], 403);
        }

        if ($cakeReq->status !== 'Quoted') {
            return response()->json(['message' => "Only custom cakes in 'Quoted' status can be accepted (current: {$cakeReq->status})."], 422);
        }

        $validated = $request->validate([
            'payment_choice' => 'nullable|in:deposit,full',
            'payment_method' => 'nullable|string',
        ]);

        $paymentChoice = $validated['payment_choice'] ?? 'deposit';
        $depositPct = (float) ($cakeReq->deposit_required_percentage ?? 50.0);
        $amountPaid = $paymentChoice === 'full' ? (float) $cakeReq->quoted_price : ((float) $cakeReq->quoted_price * ($depositPct / 100.0));

        $cakeReq->status = 'Confirmed';
        $cakeReq->deposit_paid = $amountPaid;
        $cakeReq->save();

        $cakeReq->loadMissing(['branch', 'user']);
        AuditLog::create([
            'user_id' => $user?->user_id,
            'user_name' => $user ? ($user->full_name . ' (' . $user->role . ')') : 'Customer',
            'action' => 'Custom cake quote accepted',
            'record' => $cakeReq->request_number . ($cakeReq->branch ? " · {$cakeReq->branch->branch_name}" : ""),
            'entity_type' => 'CustomCakeRequest',
            'entity_id' => (string) $cakeReq->request_id,
            'old_value' => 'Quoted',
            'new_value' => 'Confirmed (ETB ' . number_format($amountPaid, 0) . ' paid)',
            'created_at' => now(),
        ]);

        // Branch staff notification (Duplicate protected)
        $alreadyAcceptedNotif = Notification::where('branch_id', $cakeReq->branch_id)
            ->where('category', 'Orders')
            ->where('title', 'like', "%{$cakeReq->request_number}%")
            ->where('title', 'like', '%Quote Accepted%')
            ->exists();

        if (!$alreadyAcceptedNotif) {
            Notification::create([
                'user_id' => null,
                'branch_id' => $cakeReq->branch_id,
                'category' => 'Orders',
                'title' => "Quote Accepted: {$cakeReq->customer_name}",
                'message' => "Customer {$cakeReq->customer_name} accepted quote for {$cakeReq->request_number} (ETB " . number_format($amountPaid, 0) . " paid). Ready for preparation in kitchen.",
                'link' => '/admin/custom-cakes',
            ]);
        }

        // Customer confirmation notification
        if ($cakeReq->user_id) {
            $alreadyCustAccepted = Notification::where('user_id', $cakeReq->user_id)
                ->where('category', 'Orders')
                ->where('title', 'like', "%{$cakeReq->request_number}%")
                ->where('title', 'like', '%Custom Cake Confirmed%')
                ->exists();

            if (!$alreadyCustAccepted) {
                Notification::create([
                    'user_id' => $cakeReq->user_id,
                    'branch_id' => $cakeReq->branch_id,
                    'category' => 'Orders',
                    'title' => "Custom Cake Confirmed: {$cakeReq->request_number}",
                    'message' => "Your payment of ETB " . number_format($amountPaid, 0) . " is received. Kitchen baking will begin shortly.",
                    'link' => '/account?tab=custom-cakes',
                ]);
            }
        }

        return response()->json([
            'message' => 'Custom cake quote accepted and status confirmed.',
            'cake_request' => $cakeReq->load(['branch', 'images']),
            'amount_paid' => $amountPaid,
            'payment_choice' => $paymentChoice,
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $cakeReq = CustomCakeRequest::findOrFail($id);
        $user = $request->user();

        if ($user && $cakeReq->user_id && $cakeReq->user_id != $user->user_id && !in_array($user->role, ['Super Admin', 'Main Branch Manager'])) {
            return response()->json(['message' => 'Unauthorized: You can only cancel your own custom cake orders.'], 403);
        }

        if (in_array($cakeReq->status, ['Cancelled', 'Rejected', 'Delivered', 'Picked up'])) {
            return response()->json(['message' => "Cannot cancel cake request in '{$cakeReq->status}' status."], 422);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string',
        ]);

        // Evaluate refund rules based on needed_by date
        $neededDate = \Carbon\Carbon::parse($cakeReq->needed_by);
        $hoursRemaining = now()->diffInHours($neededDate, false);

        $refundEligible = 'None';
        if ($cakeReq->status === 'Submitted' || $cakeReq->status === 'Quoted') {
            $refundEligible = 'N/A (No payment was made)';
        } elseif ($hoursRemaining >= 48 && $cakeReq->status !== 'Preparing') {
            $refundEligible = '100% full deposit refund';
        } elseif ($hoursRemaining >= 24 && $cakeReq->status !== 'Preparing') {
            $refundEligible = '50% deposit refund';
        } else {
            $refundEligible = '0% (Non-refundable: less than 24 hours before needed date or baking has started)';
        }

        $oldStatus = $cakeReq->status;
        $cakeReq->status = 'Cancelled';
        $cakeReq->rejection_reason = $validated['reason'] ?? 'Cancelled by customer';
        $cakeReq->save();

        $cakeReq->loadMissing('branch');
        AuditLog::create([
            'user_id' => $user?->user_id,
            'user_name' => $user ? ($user->full_name . ' (' . $user->role . ')') : 'Customer',
            'action' => 'Custom cake cancelled',
            'record' => $cakeReq->request_number . ($cakeReq->branch ? " · {$cakeReq->branch->branch_name}" : ""),
            'entity_type' => 'CustomCakeRequest',
            'entity_id' => (string) $cakeReq->request_id,
            'old_value' => $oldStatus,
            'new_value' => "Cancelled (Refund rule: {$refundEligible})",
            'created_at' => now(),
        ]);

        // Branch staff notification (Duplicate protected)
        $alreadyCancelledCake = Notification::where('branch_id', $cakeReq->branch_id)
            ->where('category', 'Orders')
            ->where('title', 'like', "%{$cakeReq->request_number}%")
            ->where('title', 'like', '%Cancelled%')
            ->exists();

        if (!$alreadyCancelledCake) {
            Notification::create([
                'user_id' => null,
                'branch_id' => $cakeReq->branch_id,
                'category' => 'Orders',
                'title' => "Custom Cake Cancelled: {$cakeReq->request_number}",
                'message' => "Custom cake request {$cakeReq->request_number} was cancelled by {$user?->full_name}. Policy: {$refundEligible}.",
                'link' => '/admin/custom-cakes',
            ]);
        }

        return response()->json([
            'message' => 'Custom cake request cancelled.',
            'refund_policy_applied' => $refundEligible,
            'cake_request' => $cakeReq->load(['branch', 'images']),
        ]);
    }

    /**
     * Upload cake reference image with strict MIME type and size restrictions
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|file|image|mimes:jpeg,png,jpg,webp|max:5120', // restricted by type & max 5MB
        ]);

        $file = $request->file('image');
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        $path = $file->storeAs('custom_cakes', $fileName, 'public');
        $url = '/storage/' . $path;

        AuditLog::create([
            'user_id' => $request->user()?->user_id,
            'user_name' => $request->user()?->full_name ?? 'Customer',
            'action' => 'Cake reference image uploaded',
            'record' => $file->getClientOriginalName(),
            'entity_type' => 'CustomCakeImage',
            'entity_id' => '0',
            'old_value' => null,
            'new_value' => "Size: " . round($file->getSize() / 1024) . "KB, Type: " . $file->getMimeType(),
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Image successfully uploaded and security validated.',
            'image_url' => $url,
            'filename' => $fileName,
            'size_kb' => round($file->getSize() / 1024),
        ], 201);
    }

    /**
     * Converts a base64 data-URI snapshot to a disk file and returns its storage URL.
     */
    private function saveBase64Image($dataUrl, $subdir = 'custom_cakes/snapshots')
    {
        if (!$dataUrl || !str_starts_with($dataUrl, 'data:image')) {
            return $dataUrl;
        }

        if (preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $type)) {
            $data = substr($dataUrl, strpos($dataUrl, ',') + 1);
            $data = base64_decode($data);
            if ($data !== false) {
                $ext = strtolower($type[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
                if (!in_array($ext, ['jpg', 'png', 'webp'])) {
                    $ext = 'jpg';
                }
                $filename = 'snap_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                \Illuminate\Support\Facades\Storage::disk('public')->put($subdir . '/' . $filename, $data);
                return '/storage/' . $subdir . '/' . $filename;
            }
        }

        return $dataUrl;
    }
}
