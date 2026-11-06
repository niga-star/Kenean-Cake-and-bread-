<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'is_fasting')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_fasting')->default(false)->after('shelf_life_hours');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'is_fasting')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('is_fasting');
            });
        }
    }
};
