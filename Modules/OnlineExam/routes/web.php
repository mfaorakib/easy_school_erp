<?php

use Illuminate\Support\Facades\Route;
use Modules\OnlineExam\Http\Controllers\OnlineExamController;
use Modules\OnlineExam\Http\Controllers\QuestionBankController;
use Modules\OnlineExam\Http\Controllers\QuestionGroupController;
use Modules\OnlineExam\Http\Controllers\ResultController;
use Modules\OnlineExam\Http\Controllers\StudentExamController;

Route::middleware('auth')->prefix('online-exam')->name('onlineexam.')->group(function () {
    // --- Question bank (Agent A) ---
    Route::resource('groups', QuestionGroupController::class)->except('show')->parameters(['groups' => 'group']);
    Route::resource('questions', QuestionBankController::class)->except('show')->parameters(['questions' => 'question']);

    // --- Exam admin + assign questions + marking (Agent B) ---
    Route::resource('exams', OnlineExamController::class)->except('show')->parameters(['exams' => 'exam']);
    Route::get('exams/{exam}/questions', [OnlineExamController::class, 'questions'])->name('exams.questions');
    Route::put('exams/{exam}/questions', [OnlineExamController::class, 'syncQuestions'])->name('exams.questions.sync');

    Route::get('results', [ResultController::class, 'index'])->name('results.index');
    Route::get('results/{attempt}', [ResultController::class, 'show'])->name('results.show');
    Route::put('results/{attempt}/mark', [ResultController::class, 'mark'])->name('results.mark');

    // --- Student side (Agent C) ---
    Route::get('my-exams', [StudentExamController::class, 'index'])->name('student.index');
    Route::get('my-exams/{exam}/take', [StudentExamController::class, 'take'])->name('student.take');
    Route::post('my-exams/{exam}/submit', [StudentExamController::class, 'submit'])->name('student.submit');
    Route::get('my-exams/{attempt}/result', [StudentExamController::class, 'result'])->name('student.result');
});
