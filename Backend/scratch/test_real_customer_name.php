<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\CustomCakeRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomCakeController;

echo "======================================================================\n";
echo "  TEST: REAL CUSTOMER NAME SUBMISSION (NOT ABEBE KEBEDE)\n";
echo "======================================================================\n\n";

$orderCtrl = new OrderController();
$cakeCtrl = new CustomCakeController();

$firstProduct = Product::first();

// 1. Test Storefront Order Placement with Real Customer Name
$testCustomerName = "Hiwot Wolde";
$testCustomerPhone = "+251 911 777 888";

$orderReq = Request::create('/api/orders', 'POST', [
    'branch_id' => 1,
    'customer_name' => $testCustomerName,
    'customer_phone' => $testCustomerPhone,
    'fulfillment_method' => 'Pickup',
    'payment_method' => 'Pay on pickup',
    'items' => [
        [
            'product_id' => $firstProduct->product_id,
            'quantity' => 1,
        ]
    ]
]);

$orderResp = $orderCtrl->store($orderReq);
$orderData = $orderResp->getData(true);

echo "Created Order Number: " . $orderData['order_number'] . "\n";
echo "Customer Name on Order: " . ($orderData['customer_name'] ?? 'NULL') . "\n";
echo "Customer Phone on Order: " . ($orderData['customer_phone'] ?? 'NULL') . "\n";

$savedOrder = Order::where('order_number', $orderData['order_number'])->first();
if ($savedOrder->customer_name === $testCustomerName && $savedOrder->customer_phone === $testCustomerPhone) {
    echo "  [PASS] Order successfully created with the REAL customer name and phone (NOT Abebe Kebede)!\n";
} else {
    echo "  [FAIL] Order customer_name does not match!\n";
    exit(1);
}

// 2. Test Custom Cake Brief Submission with Real Customer Name
$testCakeCustomerName = "Nebil Mohammed";
$testCakeCustomerPhone = "+251 922 666 555";

$cakeReq = Request::create('/api/custom-cakes', 'POST', [
    'branch_id' => 1,
    'customer_name' => $testCakeCustomerName,
    'customer_phone' => $testCakeCustomerPhone,
    'cake_size' => 'Single Tier 8"',
    'flavor' => 'Bourbon Vanilla',
    'needed_by' => date('Y-m-d', strtotime('+3 days')),
    'design' => 'Golden accents',
    'message' => 'Happy Anniversary',
]);

$cakeResp = $cakeCtrl->store($cakeReq);
$cakeData = $cakeResp->getData(true);

echo "\nCreated Custom Cake Request Number: " . $cakeData['request_number'] . "\n";
echo "Customer Name on Cake Request: " . ($cakeData['customer_name'] ?? 'NULL') . "\n";
echo "Customer Phone on Cake Request: " . ($cakeData['customer_phone'] ?? 'NULL') . "\n";

$savedCake = CustomCakeRequest::where('request_number', $cakeData['request_number'])->first();
if ($savedCake->customer_name === $testCakeCustomerName && $savedCake->customer_phone === $testCakeCustomerPhone) {
    echo "  [PASS] Custom Cake brief successfully created with REAL customer name and phone!\n";
} else {
    echo "  [FAIL] Custom Cake customer_name does not match!\n";
    exit(1);
}

// Clean up test records
$savedOrder->items()->delete();
$savedOrder->statusHistory()->delete();
$savedOrder->delete();
$savedCake->delete();
echo "\nTest records cleaned up successfully.\n";
echo "\nALL TESTS PASSED!\n";
