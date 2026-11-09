<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

echo "========================================================\n";
echo "   🧹 SweetBakes Production Database Deep Clean\n";
echo "========================================================\n\n";

DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

// 1. Completely truncate all transactional, report, cart, order, and audit tables
$tablesToTruncate = [
    'orders',
    'order_items',
    'order_status_history',
    'payments',
    'custom_cake_requests',
    'custom_cake_images',
    'seller_daily_reports',
    'seller_report_orders',
    'seller_report_custom_cakes',
    'branch_consolidated_reports',
    'stock_transfers',
    'stock_transfer_items',
    'stock_requests',
    'stock_request_items',
    'inventory_transactions',
    'audit_logs',
    'notifications',
    'contact_inquiries',
    'newsletter_subscriptions',
    'reviews',
    'salary_correction_requests',
    'staff_reports',
    'carts',
    'cart_items',
    'wishlists',
    'addresses',
    'personal_access_tokens',
    'sessions',
    'password_reset_tokens',
    'failed_jobs',
];

foreach ($tablesToTruncate as $tbl) {
    if (DB::getSchemaBuilder()->hasTable($tbl)) {
        DB::table($tbl)->truncate();
        echo "  ✓ Truncated: {$tbl}\n";
    }
}

// 2. Clean dummy branches & ensure real Main Branch in Dessie
DB::table('branches')->truncate();
DB::statement('ALTER TABLE branches AUTO_INCREMENT = 1;');

$mainBranchId = DB::table('branches')->insertGetId([
    'branch_name' => 'Main Branch - Dessie Piazza',
    'branch_code' => 'MB-01',
    'branch_type' => 'Main',
    'region' => 'Amhara',
    'city' => 'Dessie',
    'address' => 'Piazza Commercial Center, Dessie',
    'latitude' => 11.1310000,
    'longitude' => 39.6385000,
    'phone' => '+251911223344',
    'opening_hours' => '07:00 - 21:00',
    'status' => 'Active',
    'is_shopping_enabled' => 1,
    'created_at' => now(),
    'updated_at' => now(),
]);
echo "  ✓ Configured Real Branch #{$mainBranchId}: Main Branch - Dessie Piazza\n";

// 3. Clean and associate real Dessie Delivery Zones
DB::table('delivery_zones')->truncate();
DB::statement('ALTER TABLE delivery_zones AUTO_INCREMENT = 1;');

$zones = [
    ['zone_name' => 'Piazza & Arada (Central Dessie)', 'delivery_fee' => 40.00],
    ['zone_name' => 'Hotte & Memhir Akale Wold', 'delivery_fee' => 50.00],
    ['zone_name' => 'Buanbua Wuha & Robit Road', 'delivery_fee' => 60.00],
    ['zone_name' => 'Segno Gebeya & Shell Area', 'delivery_fee' => 50.00],
    ['zone_name' => 'Dessie Menafesha & Kebele 01-10', 'delivery_fee' => 50.00],
    ['zone_name' => 'Wollo Sefer & Tita Area', 'delivery_fee' => 70.00],
];

foreach ($zones as $z) {
    DB::table('delivery_zones')->insert([
        'branch_id' => $mainBranchId,
        'zone_name' => $z['zone_name'],
        'delivery_fee' => $z['delivery_fee'],
        'status' => 'Active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
echo "  ✓ Configured 6 Real Dessie Delivery Zones for Branch #{$mainBranchId}\n";

// 4. Clean Products and seed Real Bakery Catalog
DB::table('products')->truncate();
DB::statement('ALTER TABLE products AUTO_INCREMENT = 1;');
DB::table('product_images')->truncate();
DB::table('branch_product_prices')->truncate();
DB::table('branch_inventory')->truncate();

$realProducts = [
    [
        'category_id' => 1, // Cakes
        'product_name' => 'Strawberry Shortcake',
        'description' => 'Light sponge layers filled with fresh sliced strawberries and whipped vanilla cream.',
        'base_price' => 850.00,
        'shelf_life_hours' => 48,
        'image_url' => '/mockup/product_vanilla_cake.jpg',
        'is_fasting' => 0,
    ],
    [
        'category_id' => 1, // Cakes
        'product_name' => 'Chocolate Truffle Cake',
        'description' => 'Rich Belgian dark chocolate ganache layered with moist chocolate sponge.',
        'base_price' => 950.00,
        'shelf_life_hours' => 72,
        'image_url' => '/mockup/product_chocolate_cake.jpg',
        'is_fasting' => 0,
    ],
    [
        'category_id' => 2, // Bread
        'product_name' => 'Artisan French Baguette',
        'description' => 'Traditional crusty French baguette baked fresh every morning with sourdough starter.',
        'base_price' => 85.00,
        'shelf_life_hours' => 24,
        'image_url' => '/mockup/product_fresh_bread.jpg',
        'is_fasting' => 1,
    ],
    [
        'category_id' => 3, // Pastries
        'product_name' => 'Golden Butter Croissant',
        'description' => 'Flaky, buttery layered French croissant with a crisp golden exterior and airy interior.',
        'base_price' => 95.00,
        'shelf_life_hours' => 36,
        'image_url' => '/mockup/product_croissant.jpg',
        'is_fasting' => 0,
    ],
    [
        'category_id' => 4, // Cupcakes
        'product_name' => 'Red Velvet Cupcake',
        'description' => 'Classic cocoa-buttermilk red velvet sponge crowned with cream cheese frosting.',
        'base_price' => 120.00,
        'shelf_life_hours' => 48,
        'image_url' => '/mockup/product_cupcakes.jpg',
        'is_fasting' => 0,
    ],
    [
        'category_id' => 5, // Cookies
        'product_name' => 'Double Chocolate Chip Cookie',
        'description' => 'Chewy, freshly baked cookies loaded with semi-sweet chocolate chips.',
        'base_price' => 75.00,
        'shelf_life_hours' => 120,
        'image_url' => '/mockup/shop_cookies.jpg',
        'is_fasting' => 0,
    ],
];

foreach ($realProducts as $p) {
    $pId = DB::table('products')->insertGetId([
        'category_id' => $p['category_id'],
        'product_name' => $p['product_name'],
        'description' => $p['description'],
        'base_price' => $p['base_price'],
        'shelf_life_hours' => $p['shelf_life_hours'],
        'is_fasting' => $p['is_fasting'],
        'status' => 'Active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Insert Image
    DB::table('product_images')->insert([
        'product_id' => $pId,
        'image_url' => $p['image_url'],
        'sort_order' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Initial Branch Stock: 50 on-hand
    DB::table('branch_inventory')->insert([
        'branch_id' => $mainBranchId,
        'product_id' => $pId,
        'quantity_on_hand' => 50,
        'reserved_quantity' => 0,
        'reorder_level' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
echo "  ✓ Seeded 6 Real Bakery Catalog Products with 50 Units Stock Each\n";

// 5. Clean Users: Retain Only Official Accounts (Password: Password123!)
DB::table('users')->truncate();
DB::statement('ALTER TABLE users AUTO_INCREMENT = 1;');

$superAdminId = DB::table('users')->insertGetId([
    'full_name' => 'Abebe Kebede',
    'email' => 'admin@sweetbakes.com',
    'phone' => '+251911000001',
    'password' => Hash::make('Password123!'),
    'role' => 'Super Admin',
    'branch_id' => null,
    'status' => 'Active',
    'created_at' => now(),
    'updated_at' => now(),
]);

// Also alias admin@goldencrust.com so existing login credentials work
DB::table('users')->insert([
    'full_name' => 'Abebe Kebede (HQ)',
    'email' => 'admin@goldencrust.com',
    'phone' => '+251911000002',
    'password' => Hash::make('Password123!'),
    'role' => 'Super Admin',
    'branch_id' => null,
    'status' => 'Active',
    'created_at' => now(),
    'updated_at' => now(),
]);

$managerId = DB::table('users')->insertGetId([
    'full_name' => 'Amir Hassan',
    'email' => 'amirhassen2027@gmail.com',
    'phone' => '+251921894395',
    'password' => Hash::make('Password123!'),
    'role' => 'Main Branch Manager',
    'branch_id' => $mainBranchId,
    'status' => 'Active',
    'created_at' => now(),
    'updated_at' => now(),
]);

// Set manager on branch
DB::table('branches')->where('branch_id', $mainBranchId)->update(['manager_id' => $managerId]);

$sellerId = DB::table('users')->insertGetId([
    'full_name' => 'Sara Ahmed',
    'email' => 'amirhassen2026@gmail.com',
    'phone' => '+251921894396',
    'password' => Hash::make('Password123!'),
    'role' => 'Branch Seller / Cashier',
    'branch_id' => $mainBranchId,
    'status' => 'Active',
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "  ✓ Cleaned Users: Configured 3 Official Staff Accounts\n";
echo "      - Super Admin: admin@sweetbakes.com (Password123!)\n";
echo "      - Manager: amirhassen2027@gmail.com (Password123!)\n";
echo "      - Seller: amirhassen2026@gmail.com (Password123!)\n";

// 6. Delete any test snapshot files on disk
$snapshotPath = storage_path('app/public/custom_cakes/snapshots');
if (File::isDirectory($snapshotPath)) {
    $files = File::files($snapshotPath);
    foreach ($files as $f) {
        File::delete($f->getRealPath());
    }
    echo "  ✓ Cleaned " . count($files) . " custom cake test snapshot files from disk\n";
}

DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

echo "\n========================================================\n";
echo "   ✨ DATABASE IS CLEAN, PRISTINE & READY FOR REAL DATA ✨\n";
echo "========================================================\n";
