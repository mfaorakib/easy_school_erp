<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class-teacher slot (the reference system sm_assign_class_teachers). One slot per
 * class+section+year; one-to-many teachers hang off it (class_teacher_members).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_teacher_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['class_id', 'section_id', 'academic_year_id'], 'class_teacher_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_teacher_assignments');
    }
};
