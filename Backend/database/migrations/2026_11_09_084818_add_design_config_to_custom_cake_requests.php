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
        Schema::table('custom_cake_requests', function (Blueprint $table) {
            $table->json('design_config')->nullable()->after('design');
            $table->string('snapshot_url')->nullable()->after('design_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_cake_requests', function (Blueprint $table) {
            $table->dropColumn(['design_config', 'snapshot_url']);
        });
    }
};
