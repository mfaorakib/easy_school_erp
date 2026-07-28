<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class ↔ Section pivot with its own id (the reference system sm_class_sections).
 * The id is referenced by class-teacher slots. (Shift dimension deferred.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['class_id', 'section_id', 'academic_year_id'], 'class_section_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sections');
    }
};
