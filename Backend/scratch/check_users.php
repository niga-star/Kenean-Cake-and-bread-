<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Order;
use App\Models\CustomCakeRequest;
use App\Models\CustomerReview;
use Illuminate\Support\Facades\DB;

$testIds = User::where('email', 'like', 'sara.test.%')
    ->orWhere('email', 'like', 'cust.rep.%')
    ->orWhere('email', 'like', 'super.%')
    ->orWhere('email', 'like', 'test_ethio_%')
    ->orWhere('email', 'like', 'sara.ahmed5898%')
    ->pluck('user_id')
    ->toArray();

echo "Found " . count($testIds) . " ephemeral test users: " . implode(',', $testIds) . "\n";
echo "Orders linked: " . Order::whereIn('user_id', $testIds)->count() . "\n";
echo "Custom cakes linked: " . CustomCakeRequest::whereIn('user_id', $testIds)->count() . "\n";
echo "Addresses linked: " . DB::table('addresses')->whereIn('user_id', $testIds)->count() . "\n";
echo "Reviews linked: " . DB::table('reviews')->whereIn('user_id', $testIds)->count() . "\n";
echo "Tokens linked: " . DB::table('personal_access_tokens')->where('tokenable_type', User::class)->whereIn('tokenable_id', $testIds)->count() . "\n";
