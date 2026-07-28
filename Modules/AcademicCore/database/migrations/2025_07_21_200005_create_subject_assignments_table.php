<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subject assigned to a class+section with a teacher (the reference system sm_assign_subjects).
 * "Replace on update" is enforced in the service, not by a DB unique key
 * (the no-section broadcast path may legitimately create one row per section).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['class_id', 'section_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_assignments');
    }
};
