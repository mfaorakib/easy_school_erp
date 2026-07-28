<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Homework assigned to a class+section+subject by a teacher, with a due date and
 * an optional evaluation max mark. Year-scoped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homeworks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('homework_date');
            $table->date('submission_date');
            $table->decimal('evaluation_marks', 6, 2)->nullable(); // max marks, optional
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['class_id', 'section_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homeworks');
    }
};
