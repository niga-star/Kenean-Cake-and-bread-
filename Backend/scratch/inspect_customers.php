<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Order;
use App\Models\CustomCakeRequest;

foreach ([7, 8, 11, 35, 36, 37] as $id) {
    $u = User::find($id);
    if (!$u) continue;
    $ord = Order::where('user_id', $id)->count();
    $ck = CustomCakeRequest::where('user_id', $id)->count();
    echo "{$u->email} (id {$u->user_id}, role {$u->role}): orders={$ord}, cakes={$ck}\n";
}
