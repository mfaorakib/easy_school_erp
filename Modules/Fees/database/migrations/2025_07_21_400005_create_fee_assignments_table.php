<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One fee owed by one student (a fee_master assigned to a student). Payments and
 * discounts attach here. `is_paid` is a cached roll-up updated by the service.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_master_id')->constrained('fee_masters')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('status')->default('unpaid'); // unpaid | partial | paid
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['fee_master_id', 'student_id'], 'fee_assignment_unique');
            $table->index(['student_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_assignments');
    }
};
