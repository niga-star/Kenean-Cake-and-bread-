<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Product;
use App\Models\BranchInventory;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\InventoryTransaction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class SimulatedPaymentTest extends TestCase
{
    protected User $customer;
    protected User $admin;
    protected Branch $branch;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);

        Schema::dropAllTables();

        Schema::create('branches', function (Blueprint $table) {
            $table->id('branch_id');
            $table->string('branch_name');
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

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id('transaction_id');
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

        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('address_id')->nullable();
            $table->string('fulfillment_method')->default('Pickup');
            $table->string('order_status')->default('Pending');
            $table->decimal('delivery_fee', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);
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
            $table->string('payment_method', 50);
            $table->decimal('amount', 10, 2);
            $table->string('payment_status', 50)->default('PENDING');
            $table->string('transaction_reference')->nullable();
            $table->boolean('is_test')->default(false);
            $table->text('test_details')->nullable();
            $table->text('refund_reason')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('promotions', function (Blueprint $table) {
            $table->id('promotion_id');
            $table->string('promotion_name');
            $table->string('discount_type')->default('percentage');
            $table->decimal('discount_value', 10, 2);
            $table->string('status')->default('Active');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
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

        Schema::create('app_settings', function (Blueprint $table) {
            $table->id('setting_id');
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('action');
            $table->string('record')->nullable();
            $table->string('entity_type')->nullable();
            $table->string('entity_id')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamps();
        });

        $this->branch = Branch::create([
            'branch_name' => 'Main Test Branch',
            'city' => 'Dessie',
            'address' => 'Piazza',
            'is_shopping_enabled' => true,
            'status' => 'Active',
        ]);

        $this->customer = User::create([
            'full_name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'Customer',
            'status' => 'Active',
            'phone' => '0911223344',
        ]);

        $this->admin = User::create([
            'full_name' => 'Test Super Admin',
            'email' => 'superadmin@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'Super Admin',
            'status' => 'Active',
        ]);

        $this->product = Product::create([
            'product_name' => 'Test Chocolate Cake',
            'base_price' => 500,
            'status' => 'Active',
        ]);

        BranchInventory::create([
            'branch_id' => $this->branch->branch_id,
            'product_id' => $this->product->product_id,
            'quantity_on_hand' => 100,
            'reserved_quantity' => 0,
        ]);
    }

    public function test_cash_on_pickup_creates_pending_order_immediately(): void
    {
        $payload = [
            'branch_id' => $this->branch->branch_id,
            'customer_name' => 'Abebe Kebede',
            'customer_phone' => '0911223344',
            'fulfillment_method' => 'Pickup',
            'payment_method' => 'CASH_ON_PICKUP',
            'items' => [
                ['product_id' => $this->product->product_id, 'quantity' => 2]
            ],
        ];

        $response = $this->actingAs($this->customer, 'sanctum')->postJson('/api/orders', $payload);
        $response->assertStatus(201);
        $orderNumber = $response->json('order_number');

        $order = Order::where('order_number', $orderNumber)->first();
        $this->assertNotNull($order);
        $this->assertEquals('Pickup', $order->fulfillment_method);

        $payment = Payment::where('order_id', $order->order_id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('CASH_ON_PICKUP', $payment->payment_method);
        $this->assertEquals('PENDING', $payment->payment_status);
    }

    public function test_telebirr_payment_simulation_success(): void
    {
        $orderPayload = [
            'branch_id' => $this->branch->branch_id,
            'customer_name' => 'Test Telebirr Customer',
            'customer_phone' => '0911223344',
            'fulfillment_method' => 'Pickup',
            'payment_method' => 'Telebirr',
            'items' => [
                ['product_id' => $this->product->product_id, 'quantity' => 1]
            ],
        ];

        $orderRes = $this->actingAs($this->customer, 'sanctum')->postJson('/api/orders', $orderPayload);
        $orderRes->assertStatus(201);
        $orderId = $orderRes->json('order_id');

        $simPayload = [
            'order_id' => $orderId,
            'payment_method' => 'Telebirr',
            'action' => 'success',
            'phone' => '0911223344',
        ];

        $simRes = $this->postJson('/api/payments/simulate', $simPayload);

        $simRes->assertStatus(200);
        $simRes->assertJsonFragment([
            'status' => 'success',
            'payment_status' => 'PAID_TEST',
            'order_status' => 'Confirmed',
            'is_test' => true,
        ]);

        $txRef = $simRes->json('transaction_reference');
        $this->assertStringStartsWith('TEST-TEL-', $txRef);

        $payment = Payment::where('order_id', $orderId)->first();
        $this->assertEquals('PAID_TEST', $payment->payment_status);
        $this->assertTrue((bool) $payment->is_test);
    }

    public function test_cbe_birr_payment_simulation_failure_and_retry(): void
    {
        $orderPayload = [
            'branch_id' => $this->branch->branch_id,
            'customer_name' => 'Test CBE Customer',
            'customer_phone' => '0922334455',
            'fulfillment_method' => 'Pickup',
            'payment_method' => 'CBE Birr',
            'items' => [
                ['product_id' => $this->product->product_id, 'quantity' => 1]
            ],
        ];

        $orderRes = $this->actingAs($this->customer, 'sanctum')->postJson('/api/orders', $orderPayload);
        $orderId = $orderRes->json('order_id');

        $failPayload = [
            'order_id' => $orderId,
            'payment_method' => 'CBE Birr',
            'action' => 'fail',
            'account' => '1000192837465',
            'reason' => 'Insufficient test balance',
        ];

        $failRes = $this->postJson('/api/payments/simulate', $failPayload);
        $failRes->assertStatus(422);
        $failRes->assertJsonFragment([
            'status' => 'failed',
            'payment_status' => 'FAILED_TEST',
        ]);

        $order = Order::find($orderId);
        $this->assertEquals('Pending', $order->order_status);

        $successPayload = [
            'order_id' => $orderId,
            'payment_method' => 'CBE Birr',
            'action' => 'success',
            'account' => '1000192837465',
        ];

        $successRes = $this->postJson('/api/payments/simulate', $successPayload);
        $successRes->assertStatus(200);
        $this->assertEquals('PAID_TEST', $successRes->json('payment_status'));
        $this->assertStringStartsWith('TEST-CBE-', $successRes->json('transaction_reference'));
    }

    public function test_chapa_card_payment_simulation_success(): void
    {
        $orderPayload = [
            'branch_id' => $this->branch->branch_id,
            'customer_name' => 'Chapa Card Tester',
            'customer_phone' => '0933445566',
            'fulfillment_method' => 'Pickup',
            'payment_method' => 'Chapa',
            'items' => [
                ['product_id' => $this->product->product_id, 'quantity' => 1]
            ],
        ];

        $orderRes = $this->actingAs($this->customer, 'sanctum')->postJson('/api/orders', $orderPayload);
        $orderId = $orderRes->json('order_id');

        $simPayload = [
            'order_id' => $orderId,
            'payment_method' => 'Chapa',
            'action' => 'success',
            'card_name' => 'Chapa Card Tester',
            'card_last4' => '4444',
        ];

        $simRes = $this->postJson('/api/payments/simulate', $simPayload);
        $simRes->assertStatus(200);
        $this->assertEquals('PAID_TEST', $simRes->json('payment_status'));
        $this->assertStringStartsWith('TEST-CHP-', $simRes->json('transaction_reference'));
    }

    public function test_admin_dashboard_shows_test_transactions(): void
    {
        // Place a test order first
        $order = Order::create([
            'order_number' => 'ORD-TEST-99',
            'user_id' => $this->customer->user_id,
            'customer_name' => 'Test Customer',
            'branch_id' => $this->branch->branch_id,
            'total_amount' => 500,
            'order_status' => 'Confirmed',
            'placed_at' => now(),
        ]);

        Payment::create([
            'order_id' => $order->order_id,
            'payment_method' => 'Telebirr',
            'amount' => 500,
            'payment_status' => 'PAID_TEST',
            'transaction_reference' => 'TEST-TEL-ABC12345',
            'is_test' => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/admin/payments');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'payments' => [
                '*' => [
                    'payment_id',
                    'transaction_ref',
                    'order_number',
                    'method',
                    'amount',
                    'status',
                    'is_test',
                    'customer_name',
                    'order_date',
                ]
            ],
            'summary',
        ]);

        $payments = $response->json('payments');
        $this->assertNotEmpty($payments);
        $found = collect($payments)->firstWhere('transaction_ref', 'TEST-TEL-ABC12345');
        $this->assertNotNull($found);
        $this->assertTrue($found['is_test']);
    }
}
