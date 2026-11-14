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
        // 1. Add customer_email to orders
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'customer_email')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('customer_email')->nullable()->after('customer_phone');
                $table->index('customer_email');
            });
        }

        // 2. Add customer_email to custom_cake_requests
        if (Schema::hasTable('custom_cake_requests') && !Schema::hasColumn('custom_cake_requests', 'customer_email')) {
            Schema::table('custom_cake_requests', function (Blueprint $table) {
                $table->string('customer_email')->nullable()->after('customer_phone');
                $table->index('customer_email');
            });
        }

        // 3. Ensure email_verified_at on users
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'email_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            });
        }

        // 4. Create email_verifications table
        if (!Schema::hasTable('email_verifications')) {
            Schema::create('email_verifications', function (Blueprint $table) {
                $table->id();
                $table->string('email')->index();
                $table->string('code', 10);
                $table->string('token')->nullable();
                $table->timestamp('expires_at');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'customer_email')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex(['customer_email']);
                $table->dropColumn('customer_email');
            });
        }

        if (Schema::hasTable('custom_cake_requests') && Schema::hasColumn('custom_cake_requests', 'customer_email')) {
            Schema::table('custom_cake_requests', function (Blueprint $table) {
                $table->dropIndex(['customer_email']);
                $table->dropColumn('customer_email');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'email_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('email_verified_at');
            });
        }

        Schema::dropIfExists('email_verifications');
    }
};

