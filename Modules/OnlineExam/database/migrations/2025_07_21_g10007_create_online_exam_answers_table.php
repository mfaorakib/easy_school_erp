<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A student's answer to one question within an attempt.
 *   selected_options — JSON array of chosen question_option ids (mcq)
 *   bool_answer      — chosen true/false (truefalse)
 *   text_answer      — free text (fill)
 * obtain_marks/is_correct are filled by auto-grading (objective) or manual marking (fill).
 * A null obtain_marks means the answer still awaits marking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('online_exam_attempts')->cascadeOnDelete();
            $table->foreignId('question_bank_id')->constrained('question_banks')->cascadeOnDelete();
            $table->json('selected_options')->nullable();
            $table->boolean('bool_answer')->nullable();
            $table->text('text_answer')->nullable();
            $table->decimal('obtain_marks', 6, 2)->nullable();
            $table->boolean('is_correct')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['attempt_id', 'question_bank_id'], 'oea_answer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_exam_answers');
    }
};
