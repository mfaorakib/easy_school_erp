<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-student evaluation of a homework. Created at evaluation time from the live
 * roster. One row per (homework, student).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_id')->constrained('homeworks')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->boolean('is_complete')->default(false);
            $table->decimal('obtained_marks', 6, 2)->nullable();
            $table->string('comment')->nullable();
            $table->date('evaluated_on')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['homework_id', 'student_id'], 'homework_student_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_students');
    }
};
