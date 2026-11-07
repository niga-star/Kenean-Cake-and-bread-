<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Order;
use App\Models\CustomCakeRequest;
use App\Models\Address;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomCakeController;
use App\Http\Controllers\AddressController;

$passed = 0;
$failed = 0;

function assertCheck($condition, $title) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$title}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$title}\n";
        $failed++;
    }
}

echo "======================================================================\n";
echo "  SECTION 11: REAL CUSTOMER REGISTRATION & ZERO DEFAULT USERS AUDIT\n";
echo "======================================================================\n\n";

$authCtrl = new AuthController();
$orderCtrl = new OrderController();
$cakeCtrl = new CustomCakeController();
$addrCtrl = new AddressController();

// -------------------------------------------------------------------------
// CHECKPOINT 1: Unauthenticated Guest Privacy & Zero Default Leaks
// -------------------------------------------------------------------------
echo "1. Checking Unauthenticated Guest Privacy...\n";

$guestRequest = Request::create('/api/orders', 'GET');
$ordersResp = $orderCtrl->index($guestRequest);
$ordersData = $ordersResp->getData(true);
assertCheck(empty($ordersData['orders']), "Unauthenticated guest visiting orders gets empty list (zero customer data leaked)");

$cakesResp = $cakeCtrl->index($guestRequest);
$cakesData = $cakesResp->getData(true);
assertCheck(empty($cakesData['requests']), "Unauthenticated guest visiting custom cakes gets empty list");

$addrResp = $addrCtrl->index($guestRequest);
$addrData = $addrResp->getData(true);
assertCheck(empty($addrData), "Unauthenticated guest visiting addresses gets empty list");

// -------------------------------------------------------------------------
// CHECKPOINT 2: Legitimate Customer Registration
// -------------------------------------------------------------------------
echo "\n2. Testing Customer Registration with Real Data...\n";

$testEmail = 'test.real.cust.' . time() . '@example.com';
$testPassword = 'TestPassword#2026!';
$testPhone = '+251 911 223 344';
$testName = 'Bethlehem Solomon';

$regRequest = Request::create('/api/auth/register', 'POST', [
    'full_name' => $testName,
    'email' => $testEmail,
    'phone' => $testPhone,
    'password' => $testPassword,
]);

$regResp = $authCtrl->register($regRequest);
assertCheck($regResp->getStatusCode() === 201, "Registration succeeds with 201 Created status");

$regData = $regResp->getData(true);
assertCheck(!empty($regData['token']), "Registration returns immediate valid Bearer token");
assertCheck($regData['user']['email'] === $testEmail, "Registered user email matches submitted email");
assertCheck($regData['user']['role'] === 'Customer', "New registered user role is strictly 'Customer'");

// Verify DB Record & Bcrypt Hash
$newUser = User::where('email', $testEmail)->first();
assertCheck($newUser !== null, "Real customer record exists in database");
assertCheck(Hash::check($testPassword, $newUser->password), "Password is encrypted with Bcrypt hash");
assertCheck($newUser->password !== $testPassword, "Password is NEVER stored in plain text");

// -------------------------------------------------------------------------
// CHECKPOINT 3: Duplicate Email & Invalid Validation Rejection
// -------------------------------------------------------------------------
echo "\n3. Testing Registration Constraints (Unique Email & Format)...\n";

try {
    $dupRequest = Request::create('/api/auth/register', 'POST', [
        'full_name' => 'Duplicate Person',
        'email' => $testEmail,
        'phone' => '+251 911 556 677',
        'password' => 'secretPassword123!',
    ]);
    $authCtrl->register($dupRequest);
    assertCheck(false, "Duplicate email registration rejected");
} catch (\Illuminate\Validation\ValidationException $e) {
    assertCheck(true, "Duplicate email registration properly rejected with ValidationException (unique:users,email)");
}

try {
    $invalidPhoneRequest = Request::create('/api/auth/register', 'POST', [
        'full_name' => 'Invalid Phone Person',
        'email' => 'phone.fail.' . time() . '@example.com',
        'phone' => '12345678', // Invalid phone
        'password' => 'secretPassword123!',
    ]);
    $authCtrl->register($invalidPhoneRequest);
    assertCheck(false, "Invalid phone format rejected");
} catch (\Illuminate\Validation\ValidationException $e) {
    assertCheck(true, "Invalid Ethiopian phone format rejected with Ethiopian format guidance");
}

// -------------------------------------------------------------------------
// CHECKPOINT 4: Authentication & Profile Updating
// -------------------------------------------------------------------------
echo "\n4. Testing Profile Updating...\n";

// Authenticate as the new customer
$loginRequest = Request::create('/api/auth/login', 'POST', [
    'email' => $testEmail,
    'password' => $testPassword,
]);
$loginResp = $authCtrl->login($loginRequest);
assertCheck($loginResp->getStatusCode() === 200, "Customer can log in with new credentials");

// Test updating personal information
$profileUpdateRequest = Request::create('/api/auth/profile', 'PATCH', [
    'full_name' => 'Bethlehem Solomon Updated',
    'phone' => '+251 922 998 877',
]);
$profileUpdateRequest->setUserResolver(function () use ($newUser) {
    return $newUser;
});

$updateResp = $authCtrl->updateProfile($profileUpdateRequest);
assertCheck($updateResp->getStatusCode() === 200, "Customer profile update returns 200 OK");

$newUser->refresh();
assertCheck($newUser->full_name === 'Bethlehem Solomon Updated', "Customer full name updated in database");
assertCheck($newUser->phone === '+251 922 998 877', "Customer phone number updated in database");

// Verify Audit Log
$auditLog = AuditLog::where('user_id', $newUser->user_id)->where('action', 'Profile updated')->first();
assertCheck($auditLog !== null, "Profile update recorded in system audit log");

// -------------------------------------------------------------------------
// CHECKPOINT 5: Secure Password Change
// -------------------------------------------------------------------------
echo "\n5. Testing Secure Password Change...\n";

// Attempt password change with wrong current password
$wrongPassRequest = Request::create('/api/auth/change-password', 'POST', [
    'current_password' => 'wrongOldPassword',
    'password' => 'BrandNewPassword#2026',
    'password_confirmation' => 'BrandNewPassword#2026',
]);
$wrongPassRequest->setUserResolver(function () use ($newUser) {
    return $newUser;
});

$wrongPassResp = $authCtrl->changePassword($wrongPassRequest);
assertCheck($wrongPassResp->getStatusCode() === 422, "Password update rejected with wrong current password (422)");

// Valid password change
$validPassRequest = Request::create('/api/auth/change-password', 'POST', [
    'current_password' => $testPassword,
    'password' => 'BrandNewPassword#2026',
    'password_confirmation' => 'BrandNewPassword#2026',
]);
$validPassRequest->setUserResolver(function () use ($newUser) {
    return $newUser;
});

$validPassResp = $authCtrl->changePassword($validPassRequest);
assertCheck($validPassResp->getStatusCode() === 200, "Password updated successfully with 200 OK");

// Verify old password no longer works
$oldLogin = Request::create('/api/auth/login', 'POST', [
    'email' => $testEmail,
    'password' => $testPassword,
]);
$oldLoginResp = $authCtrl->login($oldLogin);
assertCheck($oldLoginResp->getStatusCode() === 401, "Old password is invalid (401 Unauthorized)");

// Verify new password works
$newLogin = Request::create('/api/auth/login', 'POST', [
    'email' => $testEmail,
    'password' => 'BrandNewPassword#2026',
]);
$newLoginResp = $authCtrl->login($newLogin);
assertCheck($newLoginResp->getStatusCode() === 200, "New password successfully authenticates customer");

// -------------------------------------------------------------------------
// CHECKPOINT 6: Customer Isolation & Data Privacy
// -------------------------------------------------------------------------
echo "\n6. Testing Customer Isolation & Zero Cross-Customer Leakage...\n";

// Register second customer B
$emailB = 'test.real.cust.b.' . time() . '@example.com';
$userB = User::create([
    'full_name' => 'Kassahun Customer',
    'email' => $emailB,
    'phone' => '+251 933 112 233',
    'password' => Hash::make('SecretPassB#2026'),
    'role' => 'Customer',
    'status' => 'Active',
]);

// Add delivery address for Customer A
$addrA = Address::create([
    'user_id' => $newUser->user_id,
    'street' => 'Cameroon St, Villa 12',
    'city' => 'Addis Ababa',
    'region' => 'Bole',
    'is_default' => true,
]);

// Customer B queries addresses
$reqB = Request::create('/api/addresses', 'GET');
$reqB->setUserResolver(function () use ($userB) {
    return $userB;
});
$addrRespB = $addrCtrl->index($reqB);
$addrDataB = $addrRespB->getData(true);
assertCheck(empty($addrDataB), "Customer B cannot see Customer A's saved delivery addresses (returns 0 addresses)");

// Customer A queries addresses
$reqA = Request::create('/api/addresses', 'GET');
$reqA->setUserResolver(function () use ($newUser) {
    return $newUser;
});
$addrRespA = $addrCtrl->index($reqA);
$addrDataA = $addrRespA->getData(true);
assertCheck(count($addrDataA) === 1 && $addrDataA[0]['street'] === 'Cameroon St, Villa 12', "Customer A sees only their own saved address");

// Test Order Scoping Isolation
$orderA = Order::create([
    'order_number' => 'SB-TEST-PRIV-' . time(),
    'branch_id' => 1,
    'user_id' => $newUser->user_id,
    'fulfillment_method' => 'Delivery',
    'order_status' => 'Pending',
    'payment_status' => 'Pending',
    'total_amount' => 550.00,
    'placed_at' => now(),
]);

$ordersQueryB = Request::create('/api/orders', 'GET');
$ordersQueryB->setUserResolver(function () use ($userB) {
    return $userB;
});
$ordersRespB = $orderCtrl->index($ordersQueryB);
$ordersListB = $ordersRespB->getData(true)['orders'];
$bSeesA = false;
foreach ($ordersListB as $o) {
    if ($o['order_id'] === $orderA->order_id) {
        $bSeesA = true;
    }
}
assertCheck(!$bSeesA, "Customer B cannot see Customer A's orders in order list");

// Customer B attempts direct order access
request()->setUserResolver(function () use ($userB) {
    return $userB;
});
$directOrderResp = $orderCtrl->show($orderA->order_id);
assertCheck($directOrderResp->getStatusCode() === 403, "Customer B direct access to Customer A's order returns 403 Forbidden");

// -------------------------------------------------------------------------
// CLEANUP TEST USERS
// -------------------------------------------------------------------------
echo "\nCleaning up test users...\n";
$orderA->delete();
$addrA->delete();
DB::table('personal_access_tokens')->whereIn('tokenable_id', [$newUser->user_id, $userB->user_id])->delete();
$newUser->delete();
$userB->delete();
echo "Temporary test users cleaned up.\n";

echo "\n======================================================================\n";
echo "  FINAL SECTION 11 RESULTS: {$passed} Passed, {$failed} Failed\n";
echo "======================================================================\n";

if ($failed > 0) {
    exit(1);
}
