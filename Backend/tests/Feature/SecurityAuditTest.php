<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\AppSetting;
use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Cart;
use App\Models\CustomCakeRequest;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);

        Schema::dropAllTables();

        Schema::create('branches', function (Blueprint $table) {
            $table->id('branch_id');
            $table->string('branch_name');
            $table->string('branch_code')->nullable();
            $table->string('branch_type')->default('Regular');
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('address')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->string('status')->default('Active');
            $table->boolean('is_shopping_enabled')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('full_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->string('role')->default('Customer');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('status')->default('Active');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id('address_id');
            $table->unsignedBigInteger('user_id');
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('street');
            $table->string('notes')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id('zone_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('zone_name');
            $table->decimal('delivery_fee', 10, 2)->default(50);
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id('category_id');
            $table->string('category_name');
            $table->string('slug')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id('product_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('product_name');
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->string('status')->default('Active');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('branch_product_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('price', 10, 2);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('branch_inventory', function (Blueprint $table) {
            $table->id('inventory_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('reorder_level')->default(5);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('address_id')->nullable();
            $table->string('fulfillment_method')->default('Delivery');
            $table->string('order_status')->default('Pending');
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamp('placed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id('order_item_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_name');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id('history_id');
            $table->unsignedBigInteger('order_id');
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('changed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->unsignedBigInteger('order_id');
            $table->string('payment_method');
            $table->decimal('amount', 10, 2);
            $table->string('payment_status')->default('Pending');
            $table->string('transaction_reference')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->text('refund_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('custom_cake_requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->string('request_number')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('cake_size');
            $table->string('flavor');
            $table->text('design')->nullable();
            $table->json('design_config')->nullable();
            $table->string('snapshot_url')->nullable();
            $table->text('message')->nullable();
            $table->date('needed_by');
            $table->string('status')->default('Submitted');
            $table->decimal('quoted_price', 10, 2)->nullable();
            $table->decimal('deposit_required_percentage', 5, 2)->nullable();
            $table->decimal('deposit_paid', 10, 2)->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('custom_cake_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->string('image_url');
            $table->timestamps();
        });

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('product_id');
            $table->string('transaction_type');
            $table->integer('quantity');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name');
            $table->string('action');
            $table->string('record')->nullable();
            $table->string('entity_type')->nullable();
            $table->string('entity_id')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id('notification_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('category')->default('Orders');
            $table->string('title');
            $table->text('message');
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('seller_report_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('report_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->timestamps();
        });

        Schema::create('seller_report_custom_cakes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('report_id')->nullable();
            $table->unsignedBigInteger('custom_cake_id')->nullable();
            $table->timestamps();
        });

        Schema::create('seller_daily_reports', function (Blueprint $table) {
            $table->id('report_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('seller_id');
            $table->date('report_date');
            $table->string('reference_number')->nullable();
            $table->string('status')->default('draft');
            $table->decimal('booked_sales', 10, 2)->default(0);
            $table->decimal('cash_collected', 10, 2)->default(0);
            $table->decimal('card_revenue', 10, 2)->default(0);
            $table->decimal('transfer_revenue', 10, 2)->default(0);
            $table->decimal('custom_cake_deposits', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });

        Schema::create('promotions', function (Blueprint $table) {
            $table->id('promotion_id');
            $table->string('promotion_name');
            $table->string('discount_type')->default('percentage');
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->id('cart_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id('item_id');
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
        });
    }

    public function test_unauthenticated_and_cross_user_order_idor_are_blocked(): void
    {
        $branch = Branch::create(['branch_name' => 'Dessie Piazza', 'branch_type' => 'Main', 'city' => 'Dessie', 'region' => 'Amhara', 'status' => 'Active', 'is_shopping_enabled' => true]);
        $custA = User::create(['full_name' => 'Customer A', 'email' => 'a@gmail.com', 'password' => Hash::make(Str::random(16)), 'role' => 'Customer', 'status' => 'Active']);
        $custB = User::create(['full_name' => 'Customer B', 'email' => 'b@gmail.com', 'password' => Hash::make(Str::random(16)), 'role' => 'Customer', 'status' => 'Active']);

        $order = Order::create([
            'order_number' => 'ORD-5501',
            'user_id' => $custA->user_id,
            'customer_name' => 'Customer A',
            'customer_phone' => '+251911000111',
            'customer_email' => 'a@gmail.com',
            'branch_id' => $branch->branch_id,
            'fulfillment_method' => 'Delivery',
            'order_status' => 'Pending',
            'total_amount' => 1200,
            'placed_at' => now(),
        ]);

        // Unauthenticated caller must get 401 on order show, cancel, and admin release-expired
        $this->getJson("/api/orders/{$order->order_id}")->assertStatus(401);
        $this->postJson("/api/orders/{$order->order_id}/cancel")->assertStatus(401);
        $this->postJson('/api/admin/orders/release-expired')->assertStatus(401);

        // Customer B must get 403 trying to view/cancel Customer A's order or trigger release-expired
        Sanctum::actingAs($custB);
        $this->getJson("/api/orders/{$order->order_id}")->assertStatus(403);
        $this->postJson("/api/orders/{$order->order_id}/cancel")->assertStatus(403);
        $this->postJson('/api/admin/orders/release-expired')->assertStatus(403);

        // Owner Customer A can view their own order
        Sanctum::actingAs($custA);
        $this->getJson("/api/orders/{$order->order_number}")->assertStatus(200)->assertJsonPath('order_number', 'ORD-5501');
    }

    public function test_unauthenticated_and_cross_user_custom_cake_idor_and_quote_accept_blocked(): void
    {
        $branch = Branch::create(['branch_name' => 'Buabuha', 'city' => 'Dessie', 'region' => 'Amhara', 'status' => 'Active', 'is_shopping_enabled' => true]);
        $custA = User::create(['full_name' => 'Owner Cake', 'email' => 'cakea@gmail.com', 'password' => Hash::make(Str::random(16)), 'role' => 'Customer', 'status' => 'Active']);
        $custB = User::create(['full_name' => 'Other User', 'email' => 'cakeb@gmail.com', 'password' => Hash::make(Str::random(16)), 'role' => 'Customer', 'status' => 'Active']);

        $cake = CustomCakeRequest::create([
            'request_number' => 'CCR-9001',
            'user_id' => $custA->user_id,
            'branch_id' => $branch->branch_id,
            'customer_name' => 'Owner Cake',
            'customer_phone' => '+251922000111',
            'cake_size' => '8"',
            'flavor' => 'Vanilla',
            'needed_by' => now()->addDays(5)->toDateString(),
            'status' => 'Quoted',
            'quoted_price' => 3200,
        ]);

        // Unauthenticated requests must be rejected
        $this->getJson("/api/custom-cakes/{$cake->request_id}")->assertStatus(401);
        $this->postJson("/api/custom-cakes/{$cake->request_id}/accept-quote")->assertStatus(401);
        $this->postJson("/api/custom-cakes/{$cake->request_id}/cancel")->assertStatus(401);

        // Cross-customer requests must get 403
        Sanctum::actingAs($custB);
        $this->getJson("/api/custom-cakes/{$cake->request_id}")->assertStatus(403);
        $this->postJson("/api/custom-cakes/{$cake->request_id}/accept-quote")->assertStatus(403);
        $this->postJson("/api/custom-cakes/{$cake->request_id}/cancel")->assertStatus(403);

        // Owner can view & accept quote
        Sanctum::actingAs($custA);
        $this->getJson("/api/custom-cakes/{$cake->request_id}")->assertStatus(200);
        $this->postJson("/api/custom-cakes/{$cake->request_id}/accept-quote", ['payment_choice' => 'deposit'])
            ->assertStatus(200)
            ->assertJsonPath('cake_request.status', 'Confirmed');
    }

    public function test_switch_role_and_payment_branch_scoping_and_webhook_fail_closed(): void
    {
        $branchA = Branch::create(['branch_name' => 'Branch A', 'branch_type' => 'Main', 'status' => 'Active']);
        $branchB = Branch::create(['branch_name' => 'Branch B', 'branch_type' => 'Regular', 'status' => 'Active']);

        $managerA = User::create(['full_name' => 'Manager A', 'email' => 'mgra@gmail.com', 'password' => Hash::make(Str::random(16)), 'role' => 'Main Branch Manager', 'branch_id' => $branchA->branch_id, 'status' => 'Active']);
        $customer = User::create(['full_name' => 'Cust', 'email' => 'cust@gmail.com', 'password' => Hash::make(Str::random(16)), 'role' => 'Customer', 'status' => 'Active']);

        $orderB = Order::create([
            'order_number' => 'ORD-8802',
            'user_id' => $customer->user_id,
            'branch_id' => $branchB->branch_id,
            'fulfillment_method' => 'Pickup',
            'order_status' => 'Confirmed',
            'total_amount' => 800,
            'placed_at' => now(),
        ]);
        Payment::create([
            'order_id' => $orderB->order_id,
            'payment_method' => 'Chapa',
            'amount' => 800,
            'payment_status' => 'Paid',
            'transaction_reference' => 'CH-TEST01',
        ]);

        // Unauthenticated and non-Super-Admin users cannot simulate personas via switch-role
        $this->postJson('/api/auth/switch-role', ['role' => 'Super Admin'])->assertStatus(403);
        Sanctum::actingAs($customer);
        $this->postJson('/api/auth/switch-role', ['role' => 'Super Admin'])->assertStatus(403);

        // Customer blocked from admin payments
        $this->getJson('/api/admin/payments')->assertStatus(403);

        // Manager A sees 0 payments from Branch B and cannot refund Branch B orders
        Sanctum::actingAs($managerA);
        $this->getJson('/api/admin/payments')->assertStatus(200)->assertJsonCount(0, 'payments');
        $this->postJson('/api/admin/payments/refund', [
            'order_number' => 'ORD-8802',
            'amount' => 800,
            'reason' => 'Cross branch test',
        ])->assertStatus(403);

        // Webhook fails closed when no webhook secret configured in env/settings
        $this->postJson('/api/payments/webhook', ['tx_ref' => 'CH-TEST01', 'status' => 'success'], ['x-chapa-signature' => 'whsec_live_89f21a9chapa2026'])
            ->assertStatus(503);
    }

    public function test_order_creation_validates_zone_fee_and_blocks_foreign_address_id(): void
    {
        $branch = Branch::create(['branch_name' => 'Dessie Piazza', 'city' => 'Dessie', 'region' => 'Amhara', 'status' => 'Active', 'is_shopping_enabled' => true]);
        $zone1 = DeliveryZone::create(['branch_id' => $branch->branch_id, 'zone_name' => 'Piazza', 'delivery_fee' => 40, 'status' => 'Active']);
        $zone2 = DeliveryZone::create(['branch_id' => $branch->branch_id, 'zone_name' => 'Kurkur', 'delivery_fee' => 95, 'status' => 'Active']);
        $product = Product::create(['product_name' => 'Red Velvet Cake', 'base_price' => 500, 'status' => 'Active']);
        BranchInventory::create(['branch_id' => $branch->branch_id, 'product_id' => $product->product_id, 'quantity_on_hand' => 20, 'reserved_quantity' => 0]);

        $custA = User::create(['full_name' => 'Cust A', 'email' => 'ca@gmail.com', 'phone' => '+251911223344', 'password' => Hash::make(Str::random(16)), 'role' => 'Customer', 'status' => 'Active']);
        $custB = User::create(['full_name' => 'Cust B', 'email' => 'cb@gmail.com', 'phone' => '+251911556677', 'password' => Hash::make(Str::random(16)), 'role' => 'Customer', 'status' => 'Active']);
        $addrA = Address::create(['user_id' => $custA->user_id, 'street' => 'Piazza St 1', 'city' => 'Dessie', 'region' => 'Amhara']);

        // Cust B tries to use Cust A's address_id -> 403
        Sanctum::actingAs($custB);
        $this->postJson('/api/orders', [
            'branch_id' => $branch->branch_id,
            'fulfillment_method' => 'Delivery',
            'payment_method' => 'Pay on delivery',
            'address_id' => $addrA->address_id,
            'zone_id' => $zone2->zone_id,
            'items' => [['product_id' => $product->product_id, 'quantity' => 1]],
        ])->assertStatus(403);

        // Cust B selects zone2 (fee 95) -> delivery_fee must be 95, total 595
        $this->postJson('/api/orders', [
            'branch_id' => $branch->branch_id,
            'fulfillment_method' => 'Delivery',
            'payment_method' => 'Pay on delivery',
            'delivery_address' => 'Kurkur Road 4',
            'zone_id' => $zone2->zone_id,
            'items' => [['product_id' => $product->product_id, 'quantity' => 1]],
        ])->assertStatus(201)
          ->assertJsonPath('delivery_fee', '95.00');

        // Health endpoint works
        $this->getJson('/api/health')->assertStatus(200)->assertJsonPath('status', 'ok');
    }

    public function test_webhook_amount_and_currency_verification_and_broad_email_registration(): void
    {
        config(['services.chapa.webhook_secret' => 'whsec_test_secret_2026']);

        $branch = Branch::create(['branch_name' => 'Dessie Main', 'status' => 'Active']);
        $user = User::create(['full_name' => 'Test User', 'email' => 'tu@gmail.com', 'password' => Hash::make('password'), 'role' => 'Customer', 'status' => 'Active']);

        $order = Order::create([
            'order_number' => 'ORD-AMOUNT-TEST',
            'user_id' => $user->user_id,
            'branch_id' => $branch->branch_id,
            'customer_name' => 'Test User',
            'customer_phone' => '+251911000000',
            'order_status' => 'Pending',
            'total_amount' => 350.00,
        ]);

        $payment = Payment::create([
            'order_id' => $order->order_id,
            'payment_method' => 'Chapa',
            'payment_status' => 'Pending',
            'amount' => 350.00,
            'transaction_reference' => 'TX-AMOUNT-001',
        ]);

        $secret = 'whsec_test_secret_2026';

        // 1. Amount mismatch test: callback payload says amount = 200 instead of 350
        $bodyAmountMismatch = json_encode([
            'tx_ref' => 'TX-AMOUNT-001',
            'status' => 'success',
            'amount' => 200.00,
            'currency' => 'ETB',
        ]);
        $sigAmountMismatch = hash_hmac('sha256', $bodyAmountMismatch, $secret);

        $this->call('POST', '/api/payments/webhook', [], [], [], [
            'HTTP_X_CHAPA_SIGNATURE' => $sigAmountMismatch,
            'CONTENT_TYPE' => 'application/json',
        ], $bodyAmountMismatch)
            ->assertStatus(422)
            ->assertJsonPath('message', 'Payment amount mismatch: Expected ETB 350.00 but received ETB 200.00');

        // 2. Currency mismatch test: currency = USD instead of ETB
        $bodyCurrencyMismatch = json_encode([
            'tx_ref' => 'TX-AMOUNT-001',
            'status' => 'success',
            'amount' => 350.00,
            'currency' => 'USD',
        ]);
        $sigCurrencyMismatch = hash_hmac('sha256', $bodyCurrencyMismatch, $secret);

        $this->call('POST', '/api/payments/webhook', [], [], [], [
            'HTTP_X_CHAPA_SIGNATURE' => $sigCurrencyMismatch,
            'CONTENT_TYPE' => 'application/json',
        ], $bodyCurrencyMismatch)
            ->assertStatus(422)
            ->assertJsonPath('message', 'Payment currency mismatch: Expected ETB but received USD');

        // 3. Valid callback matching amount and currency confirms order
        $bodyValid = json_encode([
            'tx_ref' => 'TX-AMOUNT-001',
            'status' => 'success',
            'amount' => 350.00,
            'currency' => 'ETB',
        ]);
        $sigValid = hash_hmac('sha256', $bodyValid, $secret);

        $this->call('POST', '/api/payments/webhook', [], [], [], [
            'HTTP_X_CHAPA_SIGNATURE' => $sigValid,
            'CONTENT_TYPE' => 'application/json',
        ], $bodyValid)
            ->assertStatus(200)
            ->assertJsonPath('status', 'handled');

        $this->assertEquals('Paid', $payment->fresh()->payment_status);
        $this->assertEquals('Confirmed', $order->fresh()->order_status);

        // 4. Registration allows standard valid email domains (e.g. @outlook.com, @yahoo.com)
        $regResponse = $this->postJson('/api/auth/register', [
            'full_name' => 'Abebe Kebede',
            'email' => 'abebe@outlook.com',
            'phone' => '+251911998877',
            'password' => 'SecurePass123!',
        ]);
        $regResponse->assertStatus(201)
            ->assertJsonPath('user.email', 'abebe@outlook.com');
    }

    public function test_pos_counter_order_creation_and_stock_deduction(): void
    {
        $branch = Branch::create(['branch_name' => 'Dessie Piazza Counter', 'city' => 'Dessie', 'status' => 'Active', 'is_shopping_enabled' => true]);
        $seller = User::create([
            'full_name' => 'Counter Cashier',
            'email' => 'cashier@dessie.et',
            'password' => Hash::make('secret'),
            'role' => 'Branch Seller',
            'branch_id' => $branch->branch_id,
            'status' => 'Active',
        ]);
        $customer = User::create([
            'full_name' => 'Online Customer',
            'email' => 'cust@gmail.com',
            'password' => Hash::make('secret'),
            'role' => 'Customer',
            'status' => 'Active',
        ]);

        $product = Product::create(['product_name' => 'Chocolate Muffin', 'base_price' => 80.00, 'status' => 'Active']);
        $inv = BranchInventory::create([
            'branch_id' => $branch->branch_id,
            'product_id' => $product->product_id,
            'quantity_on_hand' => 15,
            'reserved_quantity' => 0,
            'reorder_level' => 5,
        ]);

        // 1. Customer cannot access POS endpoint -> 403 Forbidden
        Sanctum::actingAs($customer);
        $this->postJson('/api/admin/orders/pos', [
            'items' => [['product_id' => $product->product_id, 'quantity' => 2]],
            'payment_method' => 'Cash',
        ])->assertStatus(403);

        // 2. Seller creates walk-in counter sale
        Sanctum::actingAs($seller);
        $response = $this->postJson('/api/admin/orders/pos', [
            'items' => [['product_id' => $product->product_id, 'quantity' => 3]],
            'payment_method' => 'Cash',
            'amount_paid' => 300.00,
            'customer_name' => 'Walk-In Customer',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('order.order_status', 'Completed')
            ->assertJsonPath('order.fulfillment_method', 'Pickup')
            ->assertJsonPath('order.total_amount', '240.00');

        $this->assertEquals(60.0, (float) $response->json('change_due'));

        // 3. Verify stock was directly decremented (15 - 3 = 12)
        $this->assertEquals(12, $inv->fresh()->quantity_on_hand);
        $this->assertEquals(0, $inv->fresh()->reserved_quantity);

        // 4. Verify payment was recorded as Paid
        $this->assertDatabaseHas('payments', [
            'order_id' => $response->json('order.order_id'),
            'payment_status' => 'Paid',
            'payment_method' => 'Cash',
            'amount' => 240.00,
        ]);

        // 5. Verify cashier shift summary breakdown
        $shiftResponse = $this->getJson('/api/admin/reports/seller/today-orders');
        $shiftResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('shift_summary.cash_total', 240)
            ->assertJsonPath('shift_summary.total_cash_collected', 240);
    }

    public function test_automated_scheduler_and_purging_commands(): void
    {
        $oldDate = Carbon::now()->subDays(10);
        $recentDate = Carbon::now()->subDays(2);

        // 1. Setup expired and fresh carts
        DB::table('carts')->insert([
            ['session_id' => 'sess_old', 'created_at' => $oldDate, 'updated_at' => $oldDate],
            ['session_id' => 'sess_new', 'created_at' => $recentDate, 'updated_at' => $recentDate],
        ]);
        $expiredCartId = DB::table('carts')->where('session_id', 'sess_old')->value('cart_id');
        $activeCartId = DB::table('carts')->where('session_id', 'sess_new')->value('cart_id');

        // 2. Setup expired and fresh password reset tokens
        DB::table('password_reset_tokens')->insert([
            ['email' => 'old@example.com', 'token' => 'tok1', 'expires_at' => Carbon::now()->subHour(), 'used_at' => null],
            ['email' => 'used@example.com', 'token' => 'tok2', 'expires_at' => Carbon::now()->addHour(), 'used_at' => Carbon::now()->subMinute()],
            ['email' => 'valid@example.com', 'token' => 'tok3', 'expires_at' => Carbon::now()->addHour(), 'used_at' => null],
        ]);

        // Execute purge command
        $exitCode = Artisan::call('app:purge-expired');
        $this->assertEquals(0, $exitCode);

        // Verify expired cart is purged, active cart remains
        $this->assertDatabaseMissing('carts', ['cart_id' => $expiredCartId]);
        $this->assertDatabaseHas('carts', ['cart_id' => $activeCartId]);

        // Verify expired and used reset tokens are purged, valid token remains
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'old@example.com']);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'used@example.com']);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'valid@example.com']);

        // Execute release expired reservations command
        $releaseCode = Artisan::call('orders:release-expired');
        $this->assertEquals(0, $releaseCode);
    }
}
