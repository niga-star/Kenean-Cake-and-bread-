<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

$testUsers = User::where('email', 'like', 'sara.test.%')
    ->orWhere('email', 'like', 'cust.rep.%')
    ->orWhere('email', 'like', 'super.%')
    ->orWhere('email', 'like', 'test_ethio_%')
    ->orWhere('email', 'like', 'sara.ahmed5898%')
    ->get();

echo "Found " . $testUsers->count() . " test accounts to clean.\n";

$deletedCount = 0;
foreach ($testUsers as $u) {
    // Check if user has orders
    $ordersCount = DB::table('orders')->where('user_id', $u->user_id)->count();
    $cakesCount = DB::table('custom_cake_requests')->where('user_id', $u->user_id)->count();
    if ($ordersCount === 0 && $cakesCount === 0) {
        // delete tokens
        DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->where('tokenable_id', $u->user_id)
            ->delete();
        // delete addresses
        DB::table('addresses')->where('user_id', $u->user_id)->delete();
        // delete user
        $u->delete();
        $deletedCount++;
        echo "Deleted test user: {$u->email} (ID: {$u->user_id})\n";
    } else {
        echo "Skipping user {$u->email} because they have linked records.\n";
    }
}

echo "Successfully deleted {$deletedCount} ephemeral test accounts.\n";
