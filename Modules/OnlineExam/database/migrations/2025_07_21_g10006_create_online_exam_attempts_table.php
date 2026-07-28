<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One student's sitting of an online exam. One row per (exam, student).
 *   pending   — started, not yet submitted
 *   submitted — answers in; objective parts auto-graded but fill answers await marking
 *   marked    — fully graded, total_marks final
 * Year-scoped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_exam_id')->constrained('online_exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('status', ['pending', 'submitted', 'marked'])->default('pending');
            $table->decimal('total_marks', 8, 2)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['online_exam_id', 'student_id'], 'oea_exam_student_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_exam_attempts');
    }
};
