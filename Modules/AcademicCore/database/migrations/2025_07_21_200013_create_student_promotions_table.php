<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Auditable promotion snapshot (the reference system sm_student_promotions). Written each
 * time a student is promoted; the enrollment change itself is layered in
 * student_records. `academic_year_id` = the year the student was promoted INTO.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();

            $table->foreignId('previous_class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('current_class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('previous_section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('current_section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('previous_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('current_year_id')->nullable()->constrained('academic_years')->nullOnDelete();

            $table->unsignedBigInteger('admission_no')->nullable();
            $table->unsignedInteger('previous_roll_no')->nullable();
            $table->unsignedInteger('current_roll_no')->nullable();
            $table->json('student_info')->nullable();       // snapshot at promotion time
            $table->string('result_status')->default('F');  // pass/fail marker

            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_promotions');
    }
};
