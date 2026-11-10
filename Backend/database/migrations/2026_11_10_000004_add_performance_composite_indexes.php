<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Orders composite index for high-performance branch order queues
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['branch_id', 'order_status', 'placed_at'], 'idx_orders_branch_status_placed');
        });

        // 2. Inventory transactions composite index for fast ledger queries & reconciliation
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->index(['branch_id', 'product_id', 'created_at'], 'idx_inv_tx_branch_prod_created');
        });

        // 3. Seller daily reports composite index for fast manager report review & filtering
        Schema::table('seller_daily_reports', function (Blueprint $table) {
            $table->index(['branch_id', 'report_date', 'status'], 'idx_reports_branch_date_status');
        });

        // 4. Custom cake requests composite index for branch production schedules
        Schema::table('custom_cake_requests', function (Blueprint $table) {
            $table->index(['branch_id', 'status', 'needed_by'], 'idx_cakes_branch_status_needed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_branch_status_placed');
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_inv_tx_branch_prod_created');
        });

        Schema::table('seller_daily_reports', function (Blueprint $table) {
            $table->dropIndex('idx_reports_branch_date_status');
        });

        Schema::table('custom_cake_requests', function (Blueprint $table) {
            $table->dropIndex('idx_cakes_branch_status_needed');
        });
    }
};

