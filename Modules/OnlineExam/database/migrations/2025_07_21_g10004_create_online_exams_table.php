<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An online exam for a class/section/subject. Questions are attached from the
 * question bank (online_exam_questions). auto_mark toggles automatic grading of
 * objective questions (mcq / truefalse) on submit. Year-scoped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('title');
            $table->date('exam_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->text('instruction')->nullable();
            $table->boolean('auto_mark')->default(true);
            $table->boolean('is_published')->default(false);
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['class_id', 'section_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_exams');
    }
};
