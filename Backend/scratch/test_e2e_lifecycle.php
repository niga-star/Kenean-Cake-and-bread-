<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\CustomCakeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CartController;
use App\Models\CustomCakeRequest;
use App\Models\Order;
use App\Models\Branch;
use App\Models\Product;
use App\Models\User;

echo "========================================================\n";
echo "   SWEETBAKES E2E ORDER & CUSTOM CAKE LIFECYCLE TEST    \n";
echo "========================================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function assertCheck($condition, $label) {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo " [PASS] $label\n";
        $testsPassed++;
    } else {
        echo " [FAIL] $label\n";
        $testsFailed++;
    }
}

// 1. TEST UPLOAD FUNCTIONALITY
$tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'e2e_cake_' . time() . '.jpg';
$img = imagecreatetruecolor(150, 150);
$bg = imagecolorallocate($img, 222, 27, 84);
imagefilledrectangle($img, 0, 0, 150, 150, $bg);
imagejpeg($img, $tmpPath);
imagedestroy($img);

$uploadedFile = new UploadedFile($tmpPath, 'my_dream_cake.jpg', 'image/jpeg', null, true);
$reqUpload = Request::create('/api/custom-cakes/upload-image', 'POST', [], [], ['image' => $uploadedFile]);
$reqUpload->headers->set('Accept', 'application/json');

$cakeCtrl = new CustomCakeController();
$resUpload = $cakeCtrl->uploadImage($reqUpload);
$uploadedData = json_decode($resUpload->getContent(), true);

assertCheck($resUpload->status() === 201 && !empty($uploadedData['image_url']), "Image upload returns 201 with public URL: " . ($uploadedData['image_url'] ?? 'none'));
@unlink($tmpPath);

$imageUrl = $uploadedData['image_url'] ?? '/storage/custom_cakes/sample.jpg';

// 2. TEST CUSTOM CAKE SUBMISSION WITH REAL CUSTOMER
$reqCakeSubmit = Request::create('/api/custom-cakes', 'POST', [
    'branch_id' => 2, // Bole Flagship
    'customer_name' => 'Lydia Hailu',
    'customer_phone' => '+251 911 445 566',
    'cake_size' => 'Two-Tier Celebration (8" + 6")',
    'flavor' => 'Madagascar Bourbon Vanilla with Silky Ganache',
    'needed_by' => date('Y-m-d', strtotime('+4 days')),
    'design' => 'Blush pink watercolor with 24k edible gold leaf and sugar peonies',
    'message' => 'Happy Graduation Lydia!',
    'image_urls' => [$imageUrl],
]);
$reqCakeSubmit->headers->set('Accept', 'application/json');

$resCakeSubmit = $cakeCtrl->store($reqCakeSubmit);
$cakeData = json_decode($resCakeSubmit->getContent(), true);

assertCheck($resCakeSubmit->status() === 201, "Custom Cake brief created successfully (HTTP 201)");
assertCheck($cakeData['customer_name'] === 'Lydia Hailu', "Customer name saved exactly as 'Lydia Hailu' (not Abebe Kebede)");
assertCheck($cakeData['customer_phone'] === '+251 911 445 566', "Customer phone saved exactly as '+251 911 445 566'");
assertCheck($cakeData['status'] === 'Submitted', "Initial cake status is 'Submitted'");
assertCheck(!empty($cakeData['images']) && $cakeData['images'][0]['image_url'] === $imageUrl, "Reference image successfully attached in database");

$cakeId = $cakeData['request_id'];
$cakeReqNum = $cakeData['request_number'];

// 3. BRANCH ADMIN SENDS QUOTE
$adminUser = User::where('role', 'Super Admin')->first();
$reqQuote = Request::create("/api/admin/custom-cakes/{$cakeId}/quote", 'PATCH', [
    'quoted_price' => 3800,
    'deposit_required_percentage' => 50,
]);
$reqQuote->headers->set('Accept', 'application/json');
$reqQuote->setUserResolver(fn() => $adminUser);

$resQuote = $cakeCtrl->quote($reqQuote, $cakeId);
$cakeAfterQuote = CustomCakeRequest::find($cakeId);

assertCheck($cakeAfterQuote->status === 'Quoted', "Custom cake status updated to 'Quoted'");
assertCheck((float)$cakeAfterQuote->quoted_price === 3800.0, "Quoted price saved as ETB 3,800");
assertCheck((float)$cakeAfterQuote->deposit_required_percentage === 50.0, "Deposit required percentage saved as 50%");

// 4. CUSTOMER ACCEPTS QUOTE AND PAYS DEPOSIT
$reqAccept = Request::create("/api/custom-cakes/{$cakeId}/accept-quote", 'POST', [
    'payment_choice' => 'deposit',
    'payment_method' => 'Chapa',
]);
$reqAccept->headers->set('Accept', 'application/json');

$resAccept = $cakeCtrl->acceptQuote($reqAccept, $cakeId);
$cakeAfterAccept = CustomCakeRequest::find($cakeId);

assertCheck($cakeAfterAccept->status === 'Confirmed', "Cake status progressed to 'Confirmed'");
assertCheck((float)$cakeAfterAccept->deposit_paid === 1900.0, "Deposit paid recorded as ETB 1,900 (50% of 3,800)");

// 5. BRANCH PROGRESSES STAGES: Preparing -> Ready -> Delivered
$reqPrep = Request::create("/api/admin/custom-cakes/{$cakeId}/status", 'PATCH', ['status' => 'Preparing']);
$reqPrep->setUserResolver(fn() => $adminUser);
$cakeCtrl->updateStatus($reqPrep, $cakeId);
$cakeAfterPrep = CustomCakeRequest::find($cakeId);
assertCheck($cakeAfterPrep->status === 'Preparing', "Cake status progressed to 'Preparing' (Artisan Baking)");

$reqReady = Request::create("/api/admin/custom-cakes/{$cakeId}/status", 'PATCH', ['status' => 'Ready']);
$reqReady->setUserResolver(fn() => $adminUser);
$cakeCtrl->updateStatus($reqReady, $cakeId);
$cakeAfterReady = CustomCakeRequest::find($cakeId);
assertCheck($cakeAfterReady->status === 'Ready', "Cake status progressed to 'Ready' (Finished & Handover ready)");

$reqDeliver = Request::create("/api/admin/custom-cakes/{$cakeId}/status", 'PATCH', ['status' => 'Delivered']);
$reqDeliver->setUserResolver(fn() => $adminUser);
$cakeCtrl->updateStatus($reqDeliver, $cakeId);
$cakeAfterDeliver = CustomCakeRequest::find($cakeId);
assertCheck($cakeAfterDeliver->status === 'Delivered', "Cake status progressed to 'Delivered' (Order Completed)");

// 6. TEST CART SYNC WITH DATABASE
$sampleProd = Product::first();
$cartCtrl = new CartController();
$sessionId = 'test_sess_' . uniqid();

$reqCartSync = Request::create('/api/cart/sync', 'POST', [
    'branch_id' => 2,
    'session_id' => $sessionId,
    'items' => [
        ['product_id' => $sampleProd->product_id, 'quantity' => 2]
    ]
]);
$reqCartSync->headers->set('Accept', 'application/json');

$resCartSync = $cartCtrl->syncCart($reqCartSync);
assertCheck($resCartSync->status() === 200, "Cart synced with database (HTTP 200)");

$reqCartGet = Request::create('/api/cart', 'GET');
$reqCartGet->headers->set('X-Session-Id', $sessionId);
$reqCartGet->headers->set('Accept', 'application/json');

$resCartGet = $cartCtrl->getCart($reqCartGet);
$cartGetData = json_decode($resCartGet->getContent(), true);
assertCheck(count($cartGetData['items'] ?? []) > 0, "Cart retrieves synced items from database for session");

// 7. TEST STANDARD ORDER LIFECYCLE WITH REAL CUSTOMER
$orderCtrl = new OrderController();
$reqOrder = Request::create('/api/orders', 'POST', [
    'branch_id' => 2,
    'customer_name' => 'Tadesse Alemu',
    'customer_phone' => '+251 922 334 455',
    'payment_method' => 'Chapa',
    'fulfillment_method' => 'Delivery',
    'delivery_address' => 'Bole Medhanialem, Near Morning Star Mall',
    'items' => [
        ['product_id' => $sampleProd->product_id, 'quantity' => 1]
    ]
]);
$reqOrder->headers->set('Accept', 'application/json');

$resOrder = $orderCtrl->store($reqOrder);
$orderData = json_decode($resOrder->getContent(), true);

assertCheck($resOrder->status() === 201, "Order placed successfully (HTTP 201)");
assertCheck($orderData['customer_name'] === 'Tadesse Alemu', "Order customer_name is 'Tadesse Alemu' (not Abebe Kebede)");
assertCheck($orderData['customer_phone'] === '+251 922 334 455', "Order customer_phone is '+251 922 334 455'");
assertCheck($orderData['order_status'] === 'Pending', "Order initial status is 'Pending'");

$orderId = $orderData['order_id'];

// Progress Order Lifecycle
$orderStages = ['Confirmed', 'Preparing', 'Ready', 'Completed'];
foreach ($orderStages as $st) {
    $reqOrdSt = Request::create("/api/admin/orders/{$orderId}/status", 'PATCH', [
        'order_status' => $st,
        'note' => "Stage updated to $st by branch"
    ]);
    $reqOrdSt->setUserResolver(fn() => $adminUser);
    $orderCtrl->updateStatus($reqOrdSt, $orderId);
    $ordCurrent = Order::find($orderId);
    assertCheck($ordCurrent->order_status === $st, "Order lifecycle status progressed and saved as '$st'");
}

// 8. TEST CUSTOMER SEARCH FILTER
$reqSearch = Request::create('/api/orders', 'GET', ['search' => 'Tadesse Alemu']);
$reqSearch->setUserResolver(fn() => $adminUser);
$resSearch = $orderCtrl->index($reqSearch);
$searchData = json_decode($resSearch->getContent(), true);
$foundOrder = collect($searchData['orders'] ?? [])->firstWhere('customer_name', 'Tadesse Alemu');
assertCheck(!empty($foundOrder), "Admin search filter dynamically finds order by customer_name 'Tadesse Alemu'");

echo "\n========================================================\n";
echo " TEST SUMMARY: {$testsPassed} PASSED, {$testsFailed} FAILED\n";
echo "========================================================\n";
