<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A reusable question. Three answer types:
 *   mcq       — multiple choice; correct answers live in question_options (is_correct=1)
 *   truefalse — single boolean answer stored in correct_bool
 *   fill      — free-text / fill-in-the-blank; reference answer in answer_text (manual grading)
 * Optionally scoped to a class/section. Year-scoped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_group_id')->nullable()->constrained('question_groups')->nullOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->enum('type', ['mcq', 'truefalse', 'fill'])->default('mcq');
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->text('question');
            $table->decimal('marks', 6, 2)->default(1);
            $table->boolean('correct_bool')->nullable();   // for truefalse
            $table->text('answer_text')->nullable();        // for fill (reference answer)
            $table->boolean('is_active')->default(true);
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['class_id', 'section_id', 'type', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_banks');
    }
};
