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
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'customer_name')) {
                    $table->string('customer_name')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('orders', 'customer_phone')) {
                    $table->string('customer_phone')->nullable()->after('customer_name');
                }
            });
        }

        if (Schema::hasTable('custom_cake_requests')) {
            Schema::table('custom_cake_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('custom_cake_requests', 'customer_name')) {
                    $table->string('customer_name')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('custom_cake_requests', 'customer_phone')) {
                    $table->string('customer_phone')->nullable()->after('customer_name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'customer_name')) {
                    $table->dropColumn('customer_name');
                }
                if (Schema::hasColumn('orders', 'customer_phone')) {
                    $table->dropColumn('customer_phone');
                }
            });
        }

        if (Schema::hasTable('custom_cake_requests')) {
            Schema::table('custom_cake_requests', function (Blueprint $table) {
                if (Schema::hasColumn('custom_cake_requests', 'customer_name')) {
                    $table->dropColumn('customer_name');
                }
                if (Schema::hasColumn('custom_cake_requests', 'customer_phone')) {
                    $table->dropColumn('customer_phone');
                }
            });
        }
    }
};
