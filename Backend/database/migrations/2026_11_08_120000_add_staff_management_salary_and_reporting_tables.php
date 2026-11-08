<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'full_name_am')) {
                $table->string('full_name_am')->nullable()->after('full_name');
            }
            if (!Schema::hasColumn('users', 'salary')) {
                $table->decimal('salary', 12, 2)->default(0.00)->after('branch_id');
            }
            if (!Schema::hasColumn('users', 'employment_status')) {
                $table->string('employment_status')->default('Active')->after('status');
            }
            if (!Schema::hasColumn('users', 'hire_date')) {
                $table->date('hire_date')->nullable()->after('employment_status');
            }
        });

        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'max_managers')) {
                $table->integer('max_managers')->default(1)->after('manager_id');
            }
            if (!Schema::hasColumn('branches', 'max_staff')) {
                $table->integer('max_staff')->default(15)->after('max_managers');
            }
        });

        if (!Schema::hasTable('salary_correction_requests')) {
            Schema::create('salary_correction_requests', function (Blueprint $table) {
                $table->id('request_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->decimal('current_salary', 12, 2)->default(0.00);
                $table->decimal('requested_salary', 12, 2)->nullable();
                $table->text('reason');
                $table->string('status')->default('Pending'); // Pending, Approved, Rejected
                $table->text('admin_notes')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
                $table->foreign('branch_id')->references('branch_id')->on('branches')->nullOnDelete();
                $table->foreign('reviewed_by')->references('user_id')->on('users')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('staff_reports')) {
            Schema::create('staff_reports', function (Blueprint $table) {
                $table->id('report_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('branch_id');
                $table->string('report_type')->default('Daily Sales'); // Daily Sales, Delivery Summary, Shift Log, Task Report, Branch Performance
                $table->string('title');
                $table->text('content');
                $table->unsignedBigInteger('submitted_to_id')->nullable();
                $table->string('status')->default('Submitted'); // Submitted, Reviewed, Forwarded to Super Admin
                $table->text('manager_notes')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
                $table->foreign('branch_id')->references('branch_id')->on('branches')->cascadeOnDelete();
                $table->foreign('submitted_to_id')->references('user_id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_reports');
        Schema::dropIfExists('salary_correction_requests');

        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['max_managers', 'max_staff']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['full_name_am', 'salary', 'employment_status', 'hire_date']);
        });
    }
};
