<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Pivot: which question-bank items are attached to an online exam, and in what order. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_exam_id')->constrained('online_exams')->cascadeOnDelete();
            $table->foreignId('question_bank_id')->constrained('question_banks')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['online_exam_id', 'question_bank_id'], 'oeq_exam_question_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_exam_questions');
    }
};
