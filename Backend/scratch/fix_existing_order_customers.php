<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\CustomCakeRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "Updating existing orders and custom cakes with real customer names...\n";

// Real customer pool for variety on historical test orders
$customerPool = [
    ['name' => 'Sara Mengistu', 'phone' => '+251 91 234 5678'],
    ['name' => 'Daniel Worku', 'phone' => '+251 91 876 5432'],
    ['name' => 'Bethlehem Solomon', 'phone' => '+251 91 122 3344'],
    ['name' => 'Kenean Assefa', 'phone' => '+251 92 334 4556'],
    ['name' => 'Selamawit Tadesse', 'phone' => '+251 91 556 6778'],
    ['name' => 'Dawit Haile', 'phone' => '+251 93 445 5667'],
    ['name' => 'Almaz Bekele', 'phone' => '+251 91 778 8990'],
    ['name' => 'Meron Getachew', 'phone' => '+251 92 667 7889'],
];

$poolIdx = 0;

$orders = Order::all();
foreach ($orders as $order) {
    if ($order->user_id === 7) {
        $order->customer_name = 'Sara Mengistu';
        $order->customer_phone = '+251 91 234 5678';
    } elseif ($order->user_id === 8) {
        $order->customer_name = 'Daniel Worku';
        $order->customer_phone = '+251 91 876 5432';
    } else {
        $cust = $customerPool[$poolIdx % count($customerPool)];
        $order->customer_name = $cust['name'];
        $order->customer_phone = $cust['phone'];
        $poolIdx++;
    }
    $order->save();
}
echo "Updated " . $orders->count() . " orders with real customer names.\n";

$cakes = CustomCakeRequest::all();
foreach ($cakes as $cake) {
    if ($cake->user_id === 7) {
        $cake->customer_name = 'Sara Mengistu';
        $cake->customer_phone = '+251 91 234 5678';
    } elseif ($cake->user_id === 8) {
        $cake->customer_name = 'Daniel Worku';
        $cake->customer_phone = '+251 91 876 5432';
    } else {
        $cust = $customerPool[$poolIdx % count($customerPool)];
        $cake->customer_name = $cust['name'];
        $cake->customer_phone = $cust['phone'];
        $poolIdx++;
    }
    $cake->save();
}
echo "Updated " . $cakes->count() . " custom cake requests with real customer names.\n";
