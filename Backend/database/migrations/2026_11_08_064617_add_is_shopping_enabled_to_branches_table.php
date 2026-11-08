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
        if (!Schema::hasColumn('branches', 'is_shopping_enabled')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->boolean('is_shopping_enabled')->default(true)->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('branches', 'is_shopping_enabled')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->dropColumn('is_shopping_enabled');
            });
        }
    }
};
