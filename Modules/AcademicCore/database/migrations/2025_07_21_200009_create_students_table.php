<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student profile (the reference system sm_students). The base row persists across years;
 * class/section/session enrollment lives in student_records (source of truth),
 * NOT inline here. `admission_year_id` is a reference only (not a scope filter).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('guardian_id')->nullable()->constrained('guardians')->nullOnDelete();

            $table->unsignedBigInteger('admission_no')->nullable();
            $table->string('unique_id', 40)->nullable()->unique();
            $table->date('admission_date')->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('full_name')->nullable();

            $table->foreignId('gender_id')->nullable()->constrained('base_setups')->nullOnDelete();
            $table->foreignId('religion_id')->nullable()->constrained('base_setups')->nullOnDelete();
            $table->foreignId('blood_group_id')->nullable()->constrained('base_setups')->nullOnDelete();
            $table->date('date_of_birth')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('photo')->nullable();
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->text('current_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('national_id_no')->nullable();

            $table->foreignId('student_category_id')->nullable()->constrained('student_categories')->nullOnDelete();
            $table->foreignId('student_group_id')->nullable()->constrained('student_groups')->nullOnDelete();
            $table->foreignId('admission_year_id')->nullable()->constrained('academic_years')->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('admission_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
