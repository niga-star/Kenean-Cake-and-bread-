<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Branch;
use App\Models\Product;
use App\Models\BranchInventory;
use App\Models\Order;
use App\Models\StockDispatch;
use App\Models\StockDispatchItem;
use App\Models\ReceivedStock;
use App\Models\SellerDailyReport;
use App\Models\Notification;
use App\Http\Controllers\ReceivedStockController;
use App\Http\Controllers\StockDispatchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$passed = 0;
$failed = 0;

function assertTest($condition, $message) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] $message\n";
        $passed++;
    } else {
        echo "  [FAIL] $message\n";
        $failed++;
    }
}

echo "=================================================================\n";
echo "SweetBakes Platform v4.1 Comprehensive Verification Test Suite\n";
echo "=================================================================\n\n";

// -------------------------------------------------------------------------
// Setup Test Fixtures
// -------------------------------------------------------------------------
echo "Setting up fixtures...\n";

// 1. Ensure Main Branch exists with branch_type = 'Main'
$mainBranch = Branch::where('branch_type', 'Main')->first();
if (!$mainBranch) {
    $mainBranch = Branch::create([
        'branch_name' => 'Main Bole Hub',
        'branch_code' => 'MAIN-01',
        'branch_type' => 'Main',
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'status' => 'Active',
    ]);
}

// 2. Ensure Secondary Branches exist
$branch2 = Branch::where('branch_type', '!=', 'Main')->first();
if (!$branch2) {
    $branch2 = Branch::create([
        'branch_name' => 'Branch Piassa',
        'branch_code' => 'PIAS-01',
        'branch_type' => 'Standard',
        'city' => 'Addis Ababa',
        'subcity' => 'Arada',
        'status' => 'Active',
    ]);
}

$branch3 = Branch::where('branch_type', '!=', 'Main')->where('branch_id', '!=', $branch2->branch_id)->first();
if (!$branch3) {
    $branch3 = Branch::create([
        'branch_name' => 'Branch CMC',
        'branch_code' => 'CMC-01',
        'branch_type' => 'Standard',
        'city' => 'Addis Ababa',
        'subcity' => 'Yeka',
        'status' => 'Active',
    ]);
}

// Ensure Super Admin
$superAdmin = User::firstOrCreate(
    ['email' => 'admin_test@sweetbakes.test'],
    [
        'full_name' => 'Super Admin Test',
        'role' => 'Super Admin',
        'password' => Hash::make('password123'),
        'status' => 'Active',
        'email_verified_at' => now(),
    ]
);

// Main Branch Manager
$mainManager = User::firstOrCreate(
    ['email' => 'main_manager_test@sweetbakes.test'],
    [
        'full_name' => 'Main Manager Test',
        'role' => 'Main Branch Manager',
        'branch_id' => $mainBranch->branch_id,
        'password' => Hash::make('password123'),
        'status' => 'Active',
        'email_verified_at' => now(),
    ]
);
$mainBranch->manager_id = $mainManager->user_id;
$mainBranch->save();

// Branch 2 Manager
$manager2 = User::firstOrCreate(
    ['email' => 'manager2_test@sweetbakes.test'],
    [
        'full_name' => 'Manager Branch 2',
        'role' => 'Branch Manager',
        'branch_id' => $branch2->branch_id,
        'password' => Hash::make('password123'),
        'status' => 'Active',
        'email_verified_at' => now(),
    ]
);
$branch2->manager_id = $manager2->user_id;
$branch2->save();

// Branch 2 Staff / Seller
$seller2 = User::firstOrCreate(
    ['email' => 'seller2_test@sweetbakes.test'],
    [
        'full_name' => 'Seller Branch 2',
        'role' => 'Branch Seller',
        'branch_id' => $branch2->branch_id,
        'password' => Hash::make('password123'),
        'status' => 'Active',
        'email_verified_at' => now(),
    ]
);

// Branch 3 Manager
$manager3 = User::firstOrCreate(
    ['email' => 'manager3_test@sweetbakes.test'],
    [
        'full_name' => 'Manager Branch 3',
        'role' => 'Branch Manager',
        'branch_id' => $branch3->branch_id,
        'password' => Hash::make('password123'),
        'status' => 'Active',
        'email_verified_at' => now(),
    ]
);

// Ensure a test product exists
$product = Product::first();
if (!$product) {
    $category = \App\Models\Category::firstOrCreate(
        ['category_name' => 'Cakes'],
        ['description' => 'Delicious cakes']
    );
    $product = Product::create([
        'product_name' => 'Black Forest Cake Test',
        'category_id' => $category->category_id,
        'description' => 'Rich chocolate cake',
        'price' => 500.00,
        'status' => 'Available',
    ]);
}

// Setup inventories
$mainInv = BranchInventory::updateOrCreate(
    ['branch_id' => $mainBranch->branch_id, 'product_id' => $product->product_id],
    ['quantity_on_hand' => 50, 'reorder_level' => 10]
);

$branch2Inv = BranchInventory::updateOrCreate(
    ['branch_id' => $branch2->branch_id, 'product_id' => $product->product_id],
    ['quantity_on_hand' => 5, 'reorder_level' => 10]
);

echo "Fixtures initialized successfully.\n\n";

// -------------------------------------------------------------------------
// TEST 1: Stock Receive Permission Restrictions
// -------------------------------------------------------------------------
echo "--- TEST 1: Stock Receive Permission Restrictions ---\n";

$receivedStockCtrl = app(ReceivedStockController::class);

// 1.1 Super Admin cannot receive stock (403)
$req = Request::create('/api/admin/received-stocks', 'POST', [
    'product_id' => $product->product_id,
    'quantity_received' => 10,
    'dispatch_id' => 99999,
]);
$req->setUserResolver(fn() => $superAdmin);
$res = $receivedStockCtrl->store($req);
assertTest($res->getStatusCode() === 403, "Super Admin receive stock blocked with 403 Forbidden");

// 1.2 Main Branch cannot receive stock (422)
$req = Request::create('/api/admin/received-stocks', 'POST', [
    'product_id' => $product->product_id,
    'quantity_received' => 10,
    'dispatch_id' => 99999,
]);
$req->setUserResolver(fn() => $mainManager);
$res = $receivedStockCtrl->store($req);
assertTest($res->getStatusCode() === 422, "Main Branch receive stock blocked with 422 Unprocessable Entity");

// -------------------------------------------------------------------------
// TEST 2: Stock Dispatch & Two-Phase Receive Flow
// -------------------------------------------------------------------------
echo "\n--- TEST 2: Two-Phase Dispatch & Receive Flow ---\n";

$dispatchCtrl = app(StockDispatchController::class);

// 2.1 Over-dispatching beyond Main Branch stock returns 422
$req = Request::create('/api/admin/stock-dispatches', 'POST', [
    'to_branch_id' => $branch2->branch_id,
    'notes' => 'Test over-dispatch',
    'items' => [
        ['product_id' => $product->product_id, 'quantity' => 500] // Main only has 50
    ]
]);
$req->setUserResolver(fn() => $mainManager);
$res = $dispatchCtrl->store($req);
$dispatchData = json_decode($res->getContent(), true);
$dispatchId = $dispatchData['dispatch']['dispatch_id'] ?? null;

$sendReq = Request::create("/api/admin/stock-dispatches/{$dispatchId}/send", 'POST');
$sendReq->setUserResolver(fn() => $mainManager);
$sendRes = $dispatchCtrl->send($sendReq, $dispatchId);
assertTest($sendRes->getStatusCode() === 422, "Dispatch exceeding stock in hand rejected with 422");

// Clean up draft
$cancelReq = Request::create("/api/admin/stock-dispatches/{$dispatchId}/cancel", 'POST');
$cancelReq->setUserResolver(fn() => $mainManager);
$dispatchCtrl->cancel($cancelReq, $dispatchId);

// 2.2 Valid Dispatch Creation & Send
$mainStockBefore = (int) BranchInventory::where('branch_id', $mainBranch->branch_id)->where('product_id', $product->product_id)->value('quantity_on_hand');
$b2StockBefore = (int) BranchInventory::where('branch_id', $branch2->branch_id)->where('product_id', $product->product_id)->value('quantity_on_hand');

$req = Request::create('/api/admin/stock-dispatches', 'POST', [
    'to_branch_id' => $branch2->branch_id,
    'notes' => 'Test legitimate dispatch',
    'items' => [
        ['product_id' => $product->product_id, 'quantity' => 12]
    ]
]);
$req->setUserResolver(fn() => $mainManager);
$res = $dispatchCtrl->store($req);
$dispatchData = json_decode($res->getContent(), true);
$validDispatchId = $dispatchData['dispatch']['dispatch_id'];

$sendReq = Request::create("/api/admin/stock-dispatches/{$validDispatchId}/send", 'POST');
$sendReq->setUserResolver(fn() => $mainManager);
$sendRes = $dispatchCtrl->send($sendReq, $validDispatchId);
assertTest($sendRes->getStatusCode() === 200, "Valid dispatch sent successfully (200 OK)");

$mainStockAfterSend = (int) BranchInventory::where('branch_id', $mainBranch->branch_id)->where('product_id', $product->product_id)->value('quantity_on_hand');
assertTest($mainStockAfterSend === ($mainStockBefore - 12), "Main Branch stock decreased immediately by 12 upon dispatch");

// 2.3 Cross-branch unauthorized receive attempt returns 403
$req = Request::create('/api/admin/received-stocks', 'POST', [
    'dispatch_id' => $validDispatchId,
    'product_id' => $product->product_id,
    'quantity_received' => 12,
]);
$req->setUserResolver(fn() => $manager3); // Manager 3 belongs to branch 3, dispatch is for branch 2
$res = $receivedStockCtrl->store($req);
assertTest($res->getStatusCode() === 403, "Cross-branch receive attempt blocked with 403 Forbidden");

// 2.4 Over-receiving quantity beyond dispatched returns 422
$req = Request::create('/api/admin/received-stocks', 'POST', [
    'dispatch_id' => $validDispatchId,
    'product_id' => $product->product_id,
    'quantity_received' => 20, // Only 12 dispatched
]);
$req->setUserResolver(fn() => $manager2);
$res = $receivedStockCtrl->store($req);
assertTest($res->getStatusCode() === 422, "Over-receiving quantity beyond dispatched rejected with 422");

// 2.5 Valid Receive by Branch 2 Manager
$req = Request::create('/api/admin/received-stocks', 'POST', [
    'dispatch_id' => $validDispatchId,
    'product_id' => $product->product_id,
    'quantity_received' => 12,
    'unit_cost' => 300,
    'notes' => 'Received intact',
]);
$req->setUserResolver(fn() => $manager2);
$res = $receivedStockCtrl->store($req);
assertTest($res->getStatusCode() === 201, "Branch 2 received stock successfully (201 Created)");

$b2StockAfterReceive = (int) BranchInventory::where('branch_id', $branch2->branch_id)->where('product_id', $product->product_id)->value('quantity_on_hand');
assertTest($b2StockAfterReceive === ($b2StockBefore + 12), "Branch 2 stock incremented accurately by 12");

$updatedDispatch = StockDispatch::find($validDispatchId);
assertTest($updatedDispatch->status === 'received', "Stock dispatch status updated to 'received'");

// -------------------------------------------------------------------------
// TEST 3: RBAC & User Status Management
// -------------------------------------------------------------------------
echo "\n--- TEST 3: RBAC & User Status Security Controls ---\n";

$userCtrl = app(UserController::class);

// 3.1 Manager cannot change their own status (403)
$req = Request::create("/api/admin/users/{$manager2->user_id}/status", 'PATCH', [
    'status' => 'Inactive',
]);
$req->setUserResolver(fn() => $manager2);
$res = $userCtrl->updateStatus($req, $manager2->user_id);
assertTest($res->getStatusCode() === 403, "Manager changing own status blocked with 403 Forbidden");

// 3.2 Manager cannot change status of staff in another branch (403)
$b3Staff = User::firstOrCreate(
    ['email' => 'b3_staff_test@sweetbakes.test'],
    [
        'full_name' => 'B3 Staff Test',
        'role' => 'Branch Seller',
        'branch_id' => $branch3->branch_id,
        'password' => Hash::make('password123'),
        'status' => 'Active',
    ]
);
$req = Request::create("/api/admin/users/{$b3Staff->user_id}/status", 'PATCH', [
    'status' => 'Inactive',
]);
$req->setUserResolver(fn() => $manager2);
$res = $userCtrl->updateStatus($req, $b3Staff->user_id);
assertTest($res->getStatusCode() === 403, "Manager changing cross-branch staff status blocked with 403 Forbidden");

// 3.3 Suspending user without status_reason returns 422
$req = Request::create("/api/admin/users/{$seller2->user_id}/status", 'PATCH', [
    'status' => 'Suspended',
    // status_reason missing
]);
$req->setUserResolver(fn() => $manager2);
$res = $userCtrl->updateStatus($req, $seller2->user_id);
assertTest($res->getStatusCode() === 422, "Suspending user without status_reason rejected with 422");

// 3.4 Suspending user with status_reason revokes tokens immediately
$seller2->createToken('test_auth_token');
assertTest($seller2->tokens()->count() > 0, "Test token created for seller");

$req = Request::create("/api/admin/users/{$seller2->user_id}/status", 'PATCH', [
    'status' => 'Suspended',
    'status_reason' => 'Violation of health & safety policies',
]);
$req->setUserResolver(fn() => $manager2);
$res = $userCtrl->updateStatus($req, $seller2->user_id);
assertTest($res->getStatusCode() === 200, "Manager suspended branch staff successfully (200 OK)");
assertTest($seller2->fresh()->status === 'Suspended', "User status updated to Suspended in DB");
assertTest($seller2->fresh()->tokens()->count() === 0, "All user tokens immediately revoked on suspension");

// Restore seller2 to Active
$seller2->status = 'Active';
$seller2->status_reason = null;
$seller2->save();

// 3.5 Manager creating Branch Manager role returns 403
$req = Request::create('/api/admin/users', 'POST', [
    'full_name' => 'Unauthorized Manager',
    'email' => 'unauth_mgr@sweetbakes.test',
    'role' => 'Branch Manager',
    'branch_id' => $branch2->branch_id,
]);
$req->setUserResolver(fn() => $manager2);
$res = $userCtrl->store($req);
assertTest($res->getStatusCode() === 403, "Manager creating Branch Manager role blocked with 403 Forbidden");

// 3.6 Manager creating Branch Seller returns 201
$req = Request::create('/api/admin/users', 'POST', [
    'full_name' => 'Valid Branch Seller',
    'email' => 'valid_seller_' . uniqid() . '@sweetbakes.test',
    'role' => 'Branch Seller',
    'branch_id' => $branch2->branch_id,
]);
$req->setUserResolver(fn() => $manager2);
$res = $userCtrl->store($req);
assertTest($res->getStatusCode() === 201, "Manager creating Branch Seller in own branch succeeded (201 Created)");

// -------------------------------------------------------------------------
// TEST 4: Two-Factor Order Tracking Security
// -------------------------------------------------------------------------
echo "\n--- TEST 4: Two-Factor Order Tracking Security ---\n";

$orderCtrl = app(OrderController::class);

$testOrder = Order::create([
    'user_id' => $seller2->user_id,
    'order_number' => 'ORD-TEST-' . uniqid(),
    'customer_name' => 'John Tracking',
    'customer_phone' => '+251911223344',
    'customer_email' => 'john_track@sweetbakes.test',
    'branch_id' => $branch2->branch_id,
    'total_amount' => 650.00,
    'order_status' => 'Preparing',
    'placed_at' => now(),
]);

// 4.1 Order number alone fails with validation error (prevents enumeration)
$req = Request::create('/api/orders/track', 'POST', [
    'order_number' => $testOrder->order_number,
    'contact' => '',
]);
$valFailed = false;
try {
    $res = $orderCtrl->trackOrder($req);
    if ($res->getStatusCode() !== 200) $valFailed = true;
} catch (\Illuminate\Validation\ValidationException $e) {
    $valFailed = true;
}
assertTest($valFailed, "Order tracking with number alone rejected by validation");

// 4.2 Order number + wrong contact fails with 404
$req = Request::create('/api/orders/track', 'POST', [
    'order_number' => $testOrder->order_number,
    'contact' => '+251999999999',
]);
$res = $orderCtrl->trackOrder($req);
assertTest($res->getStatusCode() === 404, "Order tracking with wrong contact returns 404 Not Found");

// 4.3 Order number + correct phone succeeds with 200
$req = Request::create('/api/orders/track', 'POST', [
    'order_number' => $testOrder->order_number,
    'contact' => '+251911223344',
]);
$res = $orderCtrl->trackOrder($req);
assertTest($res->getStatusCode() === 200, "Order tracking with matching 2-factor succeeds with 200 OK");
$trackingData = json_decode($res->getContent(), true);
assertTest($trackingData['order_number'] === $testOrder->order_number, "Tracking returned correct milestone details");
assertTest(!isset($trackingData['customer_phone']), "Tracking response does not leak customer phone number");

// -------------------------------------------------------------------------
// TEST 5: Daily Sales Report Lifecycle & Summary
// -------------------------------------------------------------------------
echo "\n--- TEST 5: Sales Report Lifecycle & Summary ---\n";

$reportCtrl = app(OrderReportController::class);

// 5.1 Seller saves Draft
$req = Request::create('/api/admin/reports/seller/daily-reports', 'POST', [
    'report_date' => now()->toDateString(),
    'action' => 'draft',
    'orders' => [
        [
            'order_number' => 'ORD-101',
            'product_name' => 'Pastry Sample',
            'quantity' => 2,
            'unit_price' => 50,
        ]
    ],
    'custom_cakes' => []
]);
$req->setUserResolver(fn() => $seller2);
$res = $reportCtrl->sellerReportStore($req);
assertTest($res->getStatusCode() === 201, "Seller draft report created (201 Created)");
$draftReport = json_decode($res->getContent(), true)['report'];
assertTest($draftReport['status'] === 'Draft', "Report status is initially 'Draft'");

// 5.2 Seller submits Draft
$req = Request::create("/api/admin/reports/seller/daily-reports/{$draftReport['id']}", 'PUT', [
    'action' => 'submit',
    'orders' => [
        [
            'order_number' => 'ORD-101',
            'product_name' => 'Pastry Sample',
            'quantity' => 2,
            'unit_price' => 50,
        ]
    ],
    'custom_cakes' => []
]);
$req->setUserResolver(fn() => $seller2);
$res = $reportCtrl->sellerReportUpdate($req, $draftReport['id']);
assertTest($res->getStatusCode() === 200, "Seller report submitted (200 OK)");
$submittedReport = json_decode($res->getContent(), true)['report'];
assertTest($submittedReport['status'] === 'Submitted', "Report status transitioned to 'Submitted'");

// 5.3 Manager returns report without notes returns 422
$req = Request::create("/api/admin/reports/manager/verify/{$draftReport['id']}", 'POST', [
    'action' => 'return',
    'manager_notes' => '',
]);
$req->setUserResolver(fn() => $manager2);
$res = $reportCtrl->managerVerifyReport($req, $draftReport['id']);
assertTest($res->getStatusCode() === 422, "Manager returning report without notes rejected with 422");

// 5.4 Manager returns report with correction notes -> status becomes 'Returned'
$req = Request::create("/api/admin/reports/manager/verify/{$draftReport['id']}", 'POST', [
    'action' => 'return',
    'manager_notes' => 'Please verify pastry count line item.',
]);
$req->setUserResolver(fn() => $manager2);
$res = $reportCtrl->managerVerifyReport($req, $draftReport['id']);
assertTest($res->getStatusCode() === 200, "Manager returned report with notes (200 OK)");
$returnedReport = json_decode($res->getContent(), true)['report'];
assertTest($returnedReport['status'] === 'Returned', "Report status became 'Returned' (not 'Rejected')");

// 5.5 Seller updates returned report and submits again
$req = Request::create("/api/admin/reports/seller/daily-reports/{$draftReport['id']}", 'PUT', [
    'action' => 'submit',
    'orders' => [
        [
            'order_number' => 'ORD-101',
            'product_name' => 'Pastry Sample Updated',
            'quantity' => 3,
            'unit_price' => 50,
        ]
    ],
    'custom_cakes' => []
]);
$req->setUserResolver(fn() => $seller2);
$res = $reportCtrl->sellerReportUpdate($req, $draftReport['id']);
assertTest($res->getStatusCode() === 200, "Seller re-submitted returned report successfully");

// 5.6 Manager approves report
$req = Request::create("/api/admin/reports/manager/verify/{$draftReport['id']}", 'POST', [
    'action' => 'approve',
    'manager_notes' => 'Approved by manager.',
]);
$req->setUserResolver(fn() => $manager2);
$res = $reportCtrl->managerVerifyReport($req, $draftReport['id']);
assertTest($res->getStatusCode() === 200, "Manager approved report (200 OK)");
$approvedReport = json_decode($res->getContent(), true)['report'];
assertTest($approvedReport['status'] === 'Approved', "Report status is now 'Approved'");

// 5.7 Super Admin calls branch summary
$req = Request::create('/api/admin/reports/admin/branch-summary', 'GET');
$req->setUserResolver(fn() => $superAdmin);
$res = $reportCtrl->adminBranchSummary($req);
assertTest($res->getStatusCode() === 200, "Super Admin branch summary returns 200 OK");
$summaryData = json_decode($res->getContent(), true);
assertTest(isset($summaryData['platform_summary']['total_booked_sales']), "Summary contains total_booked_sales");
assertTest(isset($summaryData['platform_summary']['total_cash_collected']), "Summary contains total_cash_collected");

// Non-super admin calling branch summary returns 403
$req = Request::create('/api/admin/reports/admin/branch-summary', 'GET');
$req->setUserResolver(fn() => $manager2);
$blocked = false;
try {
    $reportCtrl->adminBranchSummary($req);
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    if ($e->getStatusCode() === 403) $blocked = true;
}
assertTest($blocked, "Non-Super Admin blocked from branch summary with 403 Forbidden");

// -------------------------------------------------------------------------
// TEST 6: Notification Deduplication Guard
// -------------------------------------------------------------------------
echo "\n--- TEST 6: Notification Deduplication Guard ---\n";

$nTitle = "Unique Test Notification " . uniqid();
$n1 = Notification::create([
    'user_id' => $seller2->user_id,
    'branch_id' => $branch2->branch_id,
    'category' => 'Orders',
    'title' => $nTitle,
    'message' => 'First instance',
]);
assertTest($n1->exists, "Initial notification created");

// Attempt to send identical notification within 24 hours
$n2 = Notification::create([
    'user_id' => $seller2->user_id,
    'branch_id' => $branch2->branch_id,
    'category' => 'Orders',
    'title' => $nTitle,
    'message' => 'Duplicate instance',
]);
assertTest(!$n2->exists, "Duplicate notification prevented by 24h deduplication guard");

$count = Notification::where('title', $nTitle)->count();
assertTest($count === 1, "Only 1 notification saved in database for duplicated event");

// -------------------------------------------------------------------------
// TEST 7: Indexes, Guest Linking & Financial Refund Controls
// -------------------------------------------------------------------------
echo "\n--- TEST 7: Indexes, Guest Linking & Financial Refund Controls ---\n";

// 7.1 Verify Composite Database Indexes
$orderIndices = DB::select("SHOW INDEX FROM orders WHERE Key_name = 'idx_orders_branch_status_placed'");
assertTest(!empty($orderIndices), "Composite index idx_orders_branch_status_placed active on orders");

$invIndices = DB::select("SHOW INDEX FROM inventory_transactions WHERE Key_name = 'idx_inv_tx_branch_prod_created'");
assertTest(!empty($invIndices), "Composite index idx_inv_tx_branch_prod_created active on inventory_transactions");

$reportIndices = DB::select("SHOW INDEX FROM seller_daily_reports WHERE Key_name = 'idx_reports_branch_date_status'");
assertTest(!empty($reportIndices), "Composite index idx_reports_branch_date_status active on seller_daily_reports");

// 7.2 Guest Order Linking upon Customer Registration
$guestPhone = '+251977' . mt_rand(100000, 999999);
$guestUser = User::create([
    'full_name' => 'Guest Checkout User',
    'email' => 'guest_' . time() . '_' . mt_rand(100, 999) . '@sweetbakes.local',
    'phone' => $guestPhone,
    'password' => Hash::make('password123'),
    'role' => 'Customer',
    'status' => 'Active',
]);

$guestOrder = Order::create([
    'user_id' => $guestUser->user_id,
    'order_number' => 'ORD-GUEST-' . uniqid(),
    'customer_name' => 'Guest Checkout User',
    'customer_phone' => $guestPhone,
    'branch_id' => $branch2->branch_id,
    'total_amount' => 450.00,
    'order_status' => 'Pending',
    'placed_at' => now(),
]);

$authCtrl = app(\App\Http\Controllers\AuthController::class);
$regReq = Request::create('/api/auth/register', 'POST', [
    'full_name' => 'Registered Customer',
    'email' => 'reg_' . uniqid() . '@gmail.com',
    'phone' => $guestPhone,
    'password' => 'password123',
]);
$regRes = $authCtrl->register($regReq);
assertTest($regRes->getStatusCode() === 201, "Customer registered successfully (201 Created)");
$newUserId = json_decode($regRes->getContent(), true)['user']['user_id'];
$updatedGuestOrder = $guestOrder->fresh();
assertTest((int) $updatedGuestOrder->user_id === (int) $newUserId, "Historical guest order successfully linked to newly registered user ID");

// 7.3 Financial Refund Validation
$payCtrl = app(\App\Http\Controllers\PaymentController::class);
$refundOrder = Order::create([
    'user_id' => $seller2->user_id,
    'order_number' => 'ORD-REFUND-' . uniqid(),
    'customer_name' => 'Refund Customer',
    'branch_id' => $branch2->branch_id,
    'total_amount' => 300.00,
    'order_status' => 'Confirmed',
    'placed_at' => now(),
]);

$testPayment = \App\Models\Payment::create([
    'order_id' => $refundOrder->order_id,
    'payment_method' => 'Chapa',
    'amount' => 300.00,
    'payment_status' => 'Paid',
    'transaction_reference' => 'TXN-REF-' . uniqid(),
]);

// Exceeding amount refund attempt returns 422
$req = Request::create('/api/admin/payments/refund', 'POST', [
    'order_number' => $refundOrder->order_number,
    'amount' => 500.00, // Exceeds 300
    'reason' => 'Customer requested excess refund',
]);
$req->setUserResolver(fn() => $superAdmin);
$res = $payCtrl->refund($req);
assertTest($res->getStatusCode() === 422, "Refund amount exceeding recorded payment rejected with 422");

// Valid refund succeeds
$req = Request::create('/api/admin/payments/refund', 'POST', [
    'order_number' => $refundOrder->order_number,
    'amount' => 300.00,
    'reason' => 'Defective packaging complaint',
]);
$req->setUserResolver(fn() => $superAdmin);
$res = $payCtrl->refund($req);
assertTest($res->getStatusCode() === 200, "Valid refund processed successfully (200 OK)");
assertTest($testPayment->fresh()->payment_status === 'Refunded', "Payment status marked as Refunded");
assertTest($refundOrder->fresh()->order_status === 'Cancelled', "Order status marked as Cancelled upon refund");

// -------------------------------------------------------------------------
// Summary
// -------------------------------------------------------------------------
echo "\n=================================================================\n";
echo "Test Execution Finished!\n";
echo "Total Passed: $passed\n";
echo "Total Failed: $failed\n";
echo "=================================================================\n";

exit($failed > 0 ? 1 : 0);
