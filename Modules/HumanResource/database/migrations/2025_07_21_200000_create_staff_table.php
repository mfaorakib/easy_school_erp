<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staff = HR profile extension of a user (the reference system sm_staffs). Teachers are
 * staff whose user holds the "teacher" role. Persists across years (not scoped).
 * A staff member may double as a guardian (guardian_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('staff_no')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('full_name')->nullable();

            $table->foreignId('designation_id')->nullable()->constrained('designations')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('gender_id')->nullable()->constrained('base_setups')->nullOnDelete();
            // Staff-as-parent link (the reference system sm_staffs.parent_id). Soft reference to
            // guardians (owned by AcademicCore) — no cross-module FK constraint.
            $table->unsignedBigInteger('guardian_id')->nullable();

            $table->date('date_of_birth')->nullable();
            $table->date('date_of_joining')->nullable();
            $table->date('leaving_date')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('photo')->nullable();
            $table->string('qualification')->nullable();
            $table->text('current_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->decimal('basic_salary', 12, 2)->nullable();
            $table->string('bank_name', 150)->nullable();
            $table->string('bank_account_no', 60)->nullable();
            $table->string('bank_branch', 150)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
