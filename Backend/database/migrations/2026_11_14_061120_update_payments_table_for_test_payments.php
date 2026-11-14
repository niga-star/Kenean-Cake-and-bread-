<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use ALTER TABLE statements for MySQL compatibility
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method VARCHAR(50) NOT NULL");
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_status VARCHAR(50) NOT NULL DEFAULT 'PENDING'");

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'is_test')) {
                $table->boolean('is_test')->default(false)->after('transaction_reference');
            }
            if (!Schema::hasColumn('payments', 'test_details')) {
                $table->json('test_details')->nullable()->after('is_test');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'test_details')) {
                $table->dropColumn('test_details');
            }
            if (Schema::hasColumn('payments', 'is_test')) {
                $table->dropColumn('is_test');
            }
        });

        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('Chapa','Telebirr','CBE Birr','Pay on pickup','Pay on delivery') NOT NULL");
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_status ENUM('Pending','Paid','Failed','Refunded') NOT NULL DEFAULT 'Pending'");
    }
};
