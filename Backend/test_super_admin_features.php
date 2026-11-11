<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Notification;
use App\Models\StaffReport;
use App\Models\BranchConsolidatedReport;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderReportController;

$passed = 0;
$failed = 0;

function assertTest($condition, $message) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$message}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$message}\n";
        $failed++;
    }
}

echo "=================================================================\n";
echo "SweetBakes Super Admin & Profile Photo Verification Test Suite\n";
echo "=================================================================\n\n";

// Setup Test Users
$superAdmin = User::firstOrCreate(
    ['email' => 'test_superadmin@sweetbakes.local'],
    [
        'full_name' => 'Test Super Admin',
        'password' => Hash::make('Secret123!'),
        'role' => 'Super Admin',
        'status' => 'Active',
    ]
);

$branch = Branch::first();
$branchManager = User::firstOrCreate(
    ['email' => 'test_bmanager@sweetbakes.local'],
    [
        'full_name' => 'Test Branch Manager',
        'password' => Hash::make('Secret123!'),
        'role' => 'Branch Manager',
        'branch_id' => $branch->branch_id,
        'status' => 'Active',
    ]
);

$branchSeller = User::firstOrCreate(
    ['email' => 'test_bseller@sweetbakes.local'],
    [
        'full_name' => 'Test Branch Seller',
        'password' => Hash::make('Secret123!'),
        'role' => 'Branch Seller',
        'branch_id' => $branch->branch_id,
        'status' => 'Active',
    ]
);

// -------------------------------------------------------------
// PART 1: Super Admin Dashboard Overview Real-time Data
// -------------------------------------------------------------
echo "--- PART 1: Super Admin Dashboard Overview Real-time Data ---\n";

$overviewController = new OverviewController();
$req = Request::create('/api/admin/overview', 'GET');
$req->setUserResolver(fn() => $superAdmin);

$response = $overviewController->index($req);
$data = json_decode($response->getContent(), true);

assertTest($response->getStatusCode() === 200, "Overview endpoint returns 200 OK");
assertTest(isset($data['orders_today']), "orders_today field is present: {$data['orders_today']}");
assertTest(isset($data['revenue_today']), "revenue_today field is present: ETB {$data['revenue_today']}");
assertTest(isset($data['orders_trend']), "orders_trend comparative metric is present: '{$data['orders_trend']}'");
assertTest(isset($data['revenue_trend']), "revenue_trend comparative metric is present: '{$data['revenue_trend']}'");
assertTest(is_array($data['inventory_alerts']), "inventory_alerts is an array");

// Ensure no fake mock items ('Central Pantry', 'All-Purpose Flour', etc.) in inventory alerts
$hasFakeMockPantry = false;
foreach ($data['inventory_alerts'] as $alert) {
    if (($alert['branch_name'] ?? '') === 'Central Pantry' || in_array($alert['name'] ?? '', ['All-Purpose Flour', 'Unsalted Butter', 'Fresh Milk', 'Dark Chocolate'])) {
        $hasFakeMockPantry = true;
    }
}
assertTest(!$hasFakeMockPantry, "Inventory alerts do not contain fake/mock pantry fallbacks");

// -------------------------------------------------------------
// PART 2: Super Admin Notifications Strict Isolation
// -------------------------------------------------------------
echo "\n--- PART 2: Super Admin Notifications Strict Isolation ---\n";

// Clear previous test notifications for clean test
Notification::where('user_id', $superAdmin->user_id)->delete();

// 1. Create non-report notifications (Order, Custom Cake, Dispatch, Stock Alert)
$noise1 = Notification::create([
    'user_id' => $superAdmin->user_id,
    'branch_id' => $branch->branch_id,
    'category' => 'Orders',
    'title' => 'New Order #ORD-999',
    'message' => 'New customer order placed',
    'link' => '/admin/orders',
    'is_read' => false,
]);

$noise2 = Notification::create([
    'user_id' => $superAdmin->user_id,
    'branch_id' => $branch->branch_id,
    'category' => 'Inventory',
    'title' => 'Low Stock Warning',
    'message' => 'Items running low',
    'link' => '/admin/inventory',
    'is_read' => false,
]);

$noise3 = Notification::create([
    'user_id' => $superAdmin->user_id,
    'branch_id' => $branch->branch_id,
    'category' => 'Custom Cakes',
    'title' => 'Custom Cake Request Submitted',
    'message' => 'A customer submitted a cake design',
    'link' => '/admin/custom-cakes',
    'is_read' => false,
]);

// 2. Create legitimate report notifications
$reportNotif1 = Notification::create([
    'user_id' => $superAdmin->user_id,
    'branch_id' => $branch->branch_id,
    'category' => 'Reports',
    'title' => 'New Branch Sales Report: BCR-BR1-20261111-001',
    'message' => 'Manager Test Branch Manager submitted consolidated report',
    'link' => '/admin/manager-reports?report_id=101',
    'is_read' => false,
]);

$reportNotif2 = Notification::create([
    'user_id' => $superAdmin->user_id,
    'branch_id' => $branch->branch_id,
    'category' => 'Staff Reports',
    'title' => 'Branch Report Forwarded: Weekly Sales Summary',
    'message' => 'Manager forwarded report for review',
    'link' => '/admin/manager-reports?report_id=102',
    'is_read' => false,
]);

$notifController = new NotificationController();
$notifReq = Request::create('/api/admin/notifications', 'GET');
$notifReq->setUserResolver(fn() => $superAdmin);

$notifRes = $notifController->index($notifReq);
$notifData = json_decode($notifRes->getContent(), true);

$visibleTitles = array_column($notifData['notifications'], 'title');
$visibleLinks = array_column($notifData['notifications'], 'link');

assertTest(in_array('New Branch Sales Report: BCR-BR1-20261111-001', $visibleTitles), "Super Admin sees submitted branch sales report notification");
assertTest(in_array('Branch Report Forwarded: Weekly Sales Summary', $visibleTitles), "Super Admin sees forwarded branch report notification");
assertTest(!in_array('New Order #ORD-999', $visibleTitles), "Super Admin does NOT receive order notifications");
assertTest(!in_array('Low Stock Warning', $visibleTitles), "Super Admin does NOT receive stock alert notifications");
assertTest(!in_array('Custom Cake Request Submitted', $visibleTitles), "Super Admin does NOT receive custom cake notifications");

foreach ($visibleLinks as $l) {
    assertTest(str_contains($l, 'report'), "All notifications visible to Super Admin connect to reports ({$l})");
}

// -------------------------------------------------------------
// PART 3: Worker & User Profile Photo Upload, Update and Delete
// -------------------------------------------------------------
echo "\n--- PART 3: Worker & User Profile Photo Management ---\n";

$authController = new AuthController();

// 1. Upload profile photo via base64 data image
$dummyBase64Png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

$photoReq = Request::create('/api/auth/profile-photo', 'POST', [
    'photo' => $dummyBase64Png,
]);
$photoReq->setUserResolver(fn() => $branchSeller);

$photoRes = $authController->uploadProfilePhoto($photoReq);
$photoData = json_decode($photoRes->getContent(), true);

assertTest($photoRes->getStatusCode() === 200, "Profile photo upload returns 200 OK");
assertTest(!empty($photoData['avatar_url']), "avatar_url is returned: {$photoData['avatar_url']}");
assertTest(str_starts_with($photoData['avatar_url'], '/uploads/avatars/avatar_' . $branchSeller->user_id), "avatar_url uses designated uploads folder and user ID");

$branchSeller->refresh();
assertTest($branchSeller->avatar_url === $photoData['avatar_url'], "Worker avatar_url successfully persisted to users database table");

// Verify audit log
$auditLog = DB::table('audit_logs')
    ->where('entity_id', (string)$branchSeller->user_id)
    ->where('action', 'Profile photo updated')
    ->latest('created_at')
    ->first();
assertTest($auditLog !== null, "AuditLog recorded for profile photo update");

// 2. Remove profile photo
$delPhotoReq = Request::create('/api/auth/profile-photo', 'DELETE');
$delPhotoReq->setUserResolver(fn() => $branchSeller);

$delPhotoRes = $authController->deleteProfilePhoto($delPhotoReq);
$delPhotoData = json_decode($delPhotoRes->getContent(), true);

assertTest($delPhotoRes->getStatusCode() === 200, "Profile photo removal returns 200 OK");

$branchSeller->refresh();
assertTest($branchSeller->avatar_url === null, "Worker avatar_url successfully reset to null in database");

$delAudit = DB::table('audit_logs')
    ->where('entity_id', (string)$branchSeller->user_id)
    ->where('action', 'Profile photo removed')
    ->latest('created_at')
    ->first();
assertTest($delAudit !== null, "AuditLog recorded for profile photo removal");

// Clean up test data
Notification::where('user_id', $superAdmin->user_id)->delete();

echo "\n=================================================================\n";
echo "Test Run Complete: {$passed} Passed, {$failed} Failed\n";
echo "=================================================================\n";

exit($failed > 0 ? 1 : 0);

