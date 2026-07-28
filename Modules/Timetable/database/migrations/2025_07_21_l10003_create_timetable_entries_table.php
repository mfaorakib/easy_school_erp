<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One cell of the weekly timetable: a class/section on a given weekday during a
 * given period, teaching a subject (with an optional teacher and room).
 * One entry per (class, section, day, period). Year-scoped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('day', 10); // saturday..friday
            $table->foreignId('class_period_id')->constrained('class_periods')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['class_id', 'section_id', 'day', 'class_period_id', 'academic_year_id'], 'tt_slot_unique');
            $table->index(['teacher_id', 'day', 'class_period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
    }
};
