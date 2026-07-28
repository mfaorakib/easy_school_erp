<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A student's room allocation for an academic year. Year-scoped: one active
 * allocation per student per year. Room capacity is enforced on assignment.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_dormitories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('dormitory_room_id')->constrained('dormitory_rooms')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['student_id', 'academic_year_id'], 'student_dormitory_unique');
            $table->index('dormitory_room_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_dormitories');
    }
};
