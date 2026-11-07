<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "Orders columns:\n";
print_r(Schema::getColumnListing('orders'));

echo "Custom cake requests columns:\n";
print_r(Schema::getColumnListing('custom_cake_requests'));
