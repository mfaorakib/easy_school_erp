<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Computed result per student per exam: totals, GPA, overall grade, pass/fail
 * and class rank. Rebuilt by the result service from marks + grade scale.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->decimal('total_marks', 8, 2)->default(0);
            $table->decimal('total_full_mark', 8, 2)->default(0);
            $table->decimal('gpa', 4, 2)->default(0);
            $table->string('grade')->nullable();
            $table->boolean('is_pass')->default(false);
            $table->unsignedInteger('position')->nullable();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['exam_id', 'student_id'], 'exam_result_unique');
            $table->index(['exam_id', 'class_id', 'section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
