<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Teacher(s) under a class-teacher slot (the reference system sm_class_teachers). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_teacher_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_teacher_assignment_id')->constrained('class_teacher_assignments')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('staff')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['class_teacher_assignment_id', 'teacher_id'], 'class_teacher_member_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_teacher_members');
    }
};
