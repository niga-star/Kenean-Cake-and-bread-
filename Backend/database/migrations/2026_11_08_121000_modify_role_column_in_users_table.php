<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify 'role' from restrictive ENUM to VARCHAR(100)
        DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(100) NOT NULL DEFAULT 'Customer'");
    }

    public function down(): void
    {
        // Revert to enum if ever needed
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('Super Admin', 'Main Branch Manager', 'Branch Admin', 'Branch Staff', 'Customer') NOT NULL DEFAULT 'Customer'");
    }
};
