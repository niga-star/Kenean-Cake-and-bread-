<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Branch Manager Consolidated Reports table
        if (!Schema::hasTable('branch_consolidated_reports')) {
            Schema::create('branch_consolidated_reports', function (Blueprint $table) {
                $table->id();
                $table->string('report_reference')->unique();
                $table->unsignedBigInteger('branch_id');
                $table->unsignedBigInteger('manager_id');
                $table->string('period_type')->default('Daily'); // Daily, Weekly, Monthly, Custom
                $table->date('period_start');
                $table->date('period_end');
                $table->integer('sellers_included_count')->default(0);
                $table->string('status')->default('Submitted to Super Admin'); // Draft, Submitted to Super Admin, Verified by Super Admin, Returned for Clarification
                $table->boolean('confirmed_accuracy')->default(false);
                $table->text('manager_notes')->nullable();
                $table->text('super_admin_notes')->nullable();
                $table->unsignedBigInteger('super_admin_id')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->boolean('is_locked')->default(true);

                // Consolidated Financial Totals
                $table->integer('total_regular_orders')->default(0);
                $table->integer('total_regular_products_sold')->default(0);
                $table->decimal('total_regular_revenue', 12, 2)->default(0.00);
                $table->integer('total_custom_cake_orders')->default(0);
                $table->integer('total_custom_cakes_sold')->default(0);
                $table->decimal('total_custom_cake_revenue', 12, 2)->default(0.00);
                $table->decimal('total_advance_payments', 12, 2)->default(0.00);
                $table->decimal('total_outstanding_balances', 12, 2)->default(0.00);
                $table->decimal('total_branch_revenue', 12, 2)->default(0.00);
                $table->decimal('total_payments_received', 12, 2)->default(0.00);
                $table->integer('total_orders_completed')->default(0);
                $table->integer('total_orders_pending')->default(0);
                $table->integer('total_orders_cancelled')->default(0);
                $table->longText('breakdown_json')->nullable();

                $table->timestamps();

                $table->foreign('branch_id')->references('branch_id')->on('branches')->cascadeOnDelete();
                $table->foreign('manager_id')->references('user_id')->on('users')->cascadeOnDelete();
                $table->foreign('super_admin_id')->references('user_id')->on('users')->nullOnDelete();
            });
        }

        // 2. Seller Daily Reports table
        if (!Schema::hasTable('seller_daily_reports')) {
            Schema::create('seller_daily_reports', function (Blueprint $table) {
                $table->id();
                $table->string('report_reference')->unique();
                $table->unsignedBigInteger('branch_id');
                $table->unsignedBigInteger('seller_id');
                $table->date('report_date');
                $table->string('status')->default('Draft'); // Draft, Pending Verification, Approved, Returned for Correction
                $table->unsignedBigInteger('manager_id')->nullable();
                $table->text('manager_notes')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->timestamp('returned_at')->nullable();
                $table->unsignedBigInteger('branch_report_id')->nullable();

                // Calculated Summary Totals
                $table->integer('regular_orders_count')->default(0);
                $table->integer('regular_products_sold')->default(0);
                $table->decimal('regular_order_revenue', 12, 2)->default(0.00);
                $table->integer('custom_cake_orders_count')->default(0);
                $table->integer('custom_cakes_sold')->default(0);
                $table->decimal('custom_cake_revenue', 12, 2)->default(0.00);
                $table->decimal('total_advance_payments', 12, 2)->default(0.00);
                $table->decimal('total_outstanding_balances', 12, 2)->default(0.00);
                $table->decimal('combined_daily_revenue', 12, 2)->default(0.00);
                $table->decimal('total_payments_received', 12, 2)->default(0.00);

                $table->timestamps();

                $table->foreign('branch_id')->references('branch_id')->on('branches')->cascadeOnDelete();
                $table->foreign('seller_id')->references('user_id')->on('users')->cascadeOnDelete();
                $table->foreign('manager_id')->references('user_id')->on('users')->nullOnDelete();
                $table->foreign('branch_report_id')->references('id')->on('branch_consolidated_reports')->nullOnDelete();
            });
        }

        // 3. Regular Orders in Seller Report
        if (!Schema::hasTable('seller_report_orders')) {
            Schema::create('seller_report_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('report_id');
                $table->unsignedBigInteger('order_id')->nullable();
                $table->string('order_number');
                $table->string('product_name');
                $table->string('category')->default('Pastry');
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 10, 2)->default(0.00);
                $table->decimal('total_amount', 10, 2)->default(0.00);
                $table->string('order_status')->default('Completed'); // Pending, Confirmed, Completed, Cancelled
                $table->string('payment_status')->default('Paid'); // Paid, Partially Paid, Unpaid
                $table->string('customer_info')->nullable();
                $table->timestamps();

                $table->foreign('report_id')->references('id')->on('seller_daily_reports')->cascadeOnDelete();
            });
        }

        // 4. Custom Cake Orders in Seller Report
        if (!Schema::hasTable('seller_report_custom_cakes')) {
            Schema::create('seller_report_custom_cakes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('report_id');
                $table->unsignedBigInteger('custom_cake_id')->nullable();
                $table->string('cake_order_number');
                $table->string('customer_name');
                $table->string('cake_category')->default('Birthday');
                $table->string('cake_type_design')->default('Custom Design');
                $table->string('size_weight')->default('2 kg');
                $table->integer('quantity')->default(1);
                $table->text('customization_details')->nullable();
                $table->decimal('unit_price', 10, 2)->default(0.00);
                $table->decimal('total_price', 10, 2)->default(0.00);
                $table->decimal('advance_payment', 10, 2)->default(0.00);
                $table->decimal('remaining_balance', 10, 2)->default(0.00);
                $table->date('delivery_date')->nullable();
                $table->string('order_status')->default('Confirmed'); // Pending, Confirmed, Completed, Cancelled
                $table->string('payment_status')->default('Partially Paid'); // Paid, Partially Paid, Unpaid
                $table->timestamps();

                $table->foreign('report_id')->references('id')->on('seller_daily_reports')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_report_custom_cakes');
        Schema::dropIfExists('seller_report_orders');
        Schema::dropIfExists('seller_daily_reports');
        Schema::dropIfExists('branch_consolidated_reports');
    }
};

