<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enrollment (the reference system student_records) — THE source of truth for a student's
 * class/section/year. One row per (student, class, section, year).
 *  - is_default : the student's primary enrollment within a year (one per year)
 *  - is_promote : 0 = live/current, 1 = historical (superseded by promotion)
 *  - is_graduate: student graduated out of this enrollment
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->unsignedInteger('roll_no')->nullable();
            $table->boolean('is_default')->default(true);
            $table->boolean('is_promote')->default(false);
            $table->boolean('is_graduate')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'academic_year_id', 'is_promote']);
            $table->index(['class_id', 'section_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_records');
    }
};
