<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One student's seat for one exam seat plan. `roll_no`/`class_id`/`section_id`
 * are a snapshot at generation time — a printed/pinned seat plan should stay
 * historically accurate even if a student's roll or section changes later.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_seat_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_seat_plan_id')->constrained('exam_seat_plans')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->unsignedInteger('bench_no');
            $table->unsignedTinyInteger('seat_no');
            $table->unsignedInteger('roll_no')->nullable();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->timestamps();

            $table->unique(['exam_seat_plan_id', 'student_id']);
            $table->unique(['exam_seat_plan_id', 'classroom_id', 'bench_no', 'seat_no'], 'exam_seat_unique_seat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_seat_assignments');
    }
};
