<?php

use Illuminate\Support\Facades\Route;
use Modules\Examination\Http\Controllers\ExamController;
use Modules\Examination\Http\Controllers\ExamScheduleController;
use Modules\Examination\Http\Controllers\ExamTypeController;
use Modules\Examination\Http\Controllers\GradeScaleController;
use Modules\Examination\Http\Controllers\MarksController;
use Modules\Examination\Http\Controllers\ResultController;
use Modules\Examination\Http\Controllers\SeatPlanController;

Route::middleware('auth')->prefix('exam')->name('exam.')->group(function () {
    Route::resource('types', ExamTypeController::class)->except('show')->parameters(['types' => 'type']);
    Route::resource('exams', ExamController::class)->except('show')->parameters(['exams' => 'exam']);
    Route::resource('grades', GradeScaleController::class)->except('show')->parameters(['grades' => 'grade']);

    Route::get('schedules', [ExamScheduleController::class, 'index'])->name('schedules.index');
    Route::post('schedules', [ExamScheduleController::class, 'store'])->name('schedules.store');

    Route::get('marks', [MarksController::class, 'index'])->name('marks.index');
    Route::post('marks', [MarksController::class, 'store'])->name('marks.store');

    Route::get('results', [ResultController::class, 'index'])->name('results.index');
    Route::post('results/compute', [ResultController::class, 'compute'])->name('results.compute');

    // Seat plan (Agent A)
    Route::get('seat-plan', [SeatPlanController::class, 'index'])->name('seat_plan.index');
    Route::post('seat-plan', [SeatPlanController::class, 'generate'])->name('seat_plan.generate');
    Route::get('seat-plan/{exam}', [SeatPlanController::class, 'show'])->name('seat_plan.show');
    Route::delete('seat-plan/{exam}', [SeatPlanController::class, 'destroy'])->name('seat_plan.destroy');
});
