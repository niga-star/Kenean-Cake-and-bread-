<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\CustomCakeRequest;

echo "--- FIRST 15 ORDERS ---\n";
foreach (Order::take(15)->get() as $o) {
    echo "{$o->order_number}: Customer='{$o->customer_name}', Phone='{$o->customer_phone}'\n";
}

echo "\n--- FIRST 10 CUSTOM CAKES ---\n";
foreach (CustomCakeRequest::take(10)->get() as $c) {
    echo "{$c->request_number}: Customer='{$c->customer_name}', Phone='{$c->customer_phone}'\n";
}
