<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

echo "====================================================\n";
echo " SWEETBAKES / KENAN CAKE DATABASE CLEANUP UTILITY\n";
echo "====================================================\n\n";

// 1. Verify Super Admin exists before doing anything
$superAdmin = DB::table('users')->where('role', 'Super Admin')->first();
if (!$superAdmin) {
    echo "FATAL: Super Admin record not found! Aborting cleanup.\n";
    exit(1);
}
echo "[✓] Verified Super Admin exists: {$superAdmin->email} (ID: {$superAdmin->user_id}, Name: {$superAdmin->full_name})\n";

// Verify password check
if (!Hash::check('password123', $superAdmin->password)) {
    echo "[!] Note: Super Admin password hash is preserved as-is.\n";
} else {
    echo "[✓] Super Admin password ('password123') verified valid.\n";
}

echo "\nDisabling foreign key checks...\n";
DB::statement('SET FOREIGN_KEY_CHECKS = 0');

$tablesToTruncate = [
    // Orders & Transactions
    'order_status_history',
    'order_items',
    'payments',
    'orders',
    // Custom Cakes
    'custom_cake_images',
    'custom_cake_requests',
    // Stock Transfers & Requests
    'stock_transfer_items',
    'stock_transfers',
    'stock_request_items',
    'stock_requests',
    // Inventory & Catalog
    'inventory_transactions',
    'branch_inventory',
    'branch_product_prices',
    'product_images',
    'products',
    // Branches & Zones
    'delivery_zones',
    'branches',
    // Staff Reports & Salary
    'staff_reports',
    'salary_correction_requests',
    // Customer interaction data
    'cart_items',
    'carts',
    'wishlists',
    'reviews',
    'contact_inquiries',
    'newsletter_subscriptions',
    'notifications',
    'audit_logs',
    'addresses',
    'promotion_products',
    'promotions',
    // Auth & Cache
    'personal_access_tokens',
    'cache',
    'cache_locks',
    'failed_jobs',
    'job_batches',
    'jobs',
    'password_reset_tokens',
    'sessions',
];

foreach ($tablesToTruncate as $tbl) {
    if (\Illuminate\Support\Facades\Schema::hasTable($tbl)) {
        DB::statement("TRUNCATE TABLE `{$tbl}`");
        echo "[✓] Truncated table: {$tbl}\n";
    }
}

// Clean Users table: Strictly delete everyone EXCEPT the Super Admin
$deletedUsersCount = DB::table('users')
    ->where('user_id', '!=', $superAdmin->user_id)
    ->delete();

echo "[✓] Removed {$deletedUsersCount} test/dummy users from 'users' table.\n";

// Ensure Super Admin branch_id is null and status is Active
DB::table('users')
    ->where('user_id', $superAdmin->user_id)
    ->update([
        'branch_id' => null,
        'status' => 'Active',
        'employment_status' => 'Active',
        'updated_at' => now(),
    ]);

echo "[✓] Cleaned Super Admin user state (branch_id set to null, status: Active).\n";

// Reset auto-increment on branches and products for clean 1-based IDs
DB::statement('ALTER TABLE branches AUTO_INCREMENT = 1');
DB::statement('ALTER TABLE products AUTO_INCREMENT = 1');
DB::statement('ALTER TABLE orders AUTO_INCREMENT = 1');
DB::statement('ALTER TABLE custom_cake_requests AUTO_INCREMENT = 1');
DB::statement('ALTER TABLE staff_reports AUTO_INCREMENT = 1');

echo "\nRe-enabling foreign key checks...\n";
DB::statement('SET FOREIGN_KEY_CHECKS = 1');

echo "\n====================================================\n";
echo " CLEANUP VERIFICATION & POST-CHECK\n";
echo "====================================================\n";

$remainingUsers = DB::table('users')->get();
echo "Remaining Users count: " . $remainingUsers->count() . "\n";
foreach ($remainingUsers as $u) {
    echo " - ID: {$u->user_id} | Email: {$u->email} | Role: {$u->role} | Branch: " . ($u->branch_id ?? 'None') . "\n";
}

$branchesCount = DB::table('branches')->count();
$productsCount = DB::table('products')->count();
$ordersCount = DB::table('orders')->count();
$customCakesCount = DB::table('custom_cake_requests')->count();
$inventoryCount = DB::table('branch_inventory')->count();
$reportsCount = DB::table('staff_reports')->count();
$settingsCount = DB::table('app_settings')->count();
$categoriesCount = DB::table('categories')->count();

echo "Branches: {$branchesCount}\n";
echo "Products: {$productsCount}\n";
echo "Orders: {$ordersCount}\n";
echo "Custom Cake Requests: {$customCakesCount}\n";
echo "Branch Inventory: {$inventoryCount}\n";
echo "Staff Reports: {$reportsCount}\n";
echo "App Settings preserved: {$settingsCount}\n";
echo "Categories preserved: {$categoriesCount}\n";

$recheckAdmin = DB::table('users')->where('email', $superAdmin->email)->first();
$passOk = Hash::check('password123', $recheckAdmin->password);
echo "Super Admin Login Authenticity check: " . ($passOk ? "PASSED (Credentials Intact)" : "FAILED") . "\n";

echo "\nDatabase cleanup completed successfully!\n";

