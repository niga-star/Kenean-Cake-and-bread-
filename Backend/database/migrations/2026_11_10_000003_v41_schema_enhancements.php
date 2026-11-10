<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Fix longitude precision to DECIMAL(11,8) on branches
        DB::statement("ALTER TABLE branches MODIFY COLUMN longitude DECIMAL(11, 8) NULL");

        // 2. Add status_reason to users table
        if (!Schema::hasColumn('users', 'status_reason')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('status_reason')->nullable()->after('status');
            });
        }

        // 3. Create employee_profiles table
        if (!Schema::hasTable('employee_profiles')) {
            Schema::create('employee_profiles', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->primary();
                $table->decimal('salary', 10, 2)->default(0.00);
                $table->timestamps();

                $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            });

            // Populate from existing users salary data
            $staffUsers = DB::table('users')->where('role', '!=', 'Customer')->get();
            foreach ($staffUsers as $u) {
                DB::table('employee_profiles')->insertOrIgnore([
                    'user_id' => $u->user_id,
                    'salary' => $u->salary ?? 0.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Create stock_dispatches table
        if (!Schema::hasTable('stock_dispatches')) {
            Schema::create('stock_dispatches', function (Blueprint $table) {
                $table->id('dispatch_id');
                $table->unsignedBigInteger('from_branch_id');
                $table->unsignedBigInteger('to_branch_id');
                $table->enum('status', ['draft', 'in_transit', 'partially_received', 'received', 'cancelled'])->default('draft');
                $table->string('reference_number', 100)->unique();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();

                $table->foreign('from_branch_id')->references('branch_id')->on('branches')->cascadeOnDelete();
                $table->foreign('to_branch_id')->references('branch_id')->on('branches')->cascadeOnDelete();
                $table->foreign('created_by')->references('user_id')->on('users')->nullOnDelete();
            });
        }

        // 5. Create stock_dispatch_items table
        if (!Schema::hasTable('stock_dispatch_items')) {
            Schema::create('stock_dispatch_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('dispatch_id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedInteger('quantity_dispatched');
                $table->unsignedInteger('quantity_received')->default(0);
                $table->timestamps();

                $table->foreign('dispatch_id')->references('dispatch_id')->on('stock_dispatches')->cascadeOnDelete();
                $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
            });
        }

        // 6. Add dispatch_id to received_stocks
        if (!Schema::hasColumn('received_stocks', 'dispatch_id')) {
            Schema::table('received_stocks', function (Blueprint $table) {
                $table->unsignedBigInteger('dispatch_id')->nullable()->after('branch_id');
                $table->foreign('dispatch_id')->references('dispatch_id')->on('stock_dispatches')->nullOnDelete();
            });
        }

        // 7. Add updated_by to branch_product_prices
        if (Schema::hasTable('branch_product_prices') && !Schema::hasColumn('branch_product_prices', 'updated_by')) {
            Schema::table('branch_product_prices', function (Blueprint $table) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('active');
                $table->foreign('updated_by')->references('user_id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('received_stocks', 'dispatch_id')) {
            Schema::table('received_stocks', function (Blueprint $table) {
                $table->dropForeign(['dispatch_id']);
                $table->dropColumn('dispatch_id');
            });
        }

        Schema::dropIfExists('stock_dispatch_items');
        Schema::dropIfExists('stock_dispatches');
        Schema::dropIfExists('employee_profiles');

        if (Schema::hasColumn('users', 'status_reason')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('status_reason');
            });
        }

        if (Schema::hasColumn('branch_product_prices', 'updated_by')) {
            Schema::table('branch_product_prices', function (Blueprint $table) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            });
        }
    }
};

