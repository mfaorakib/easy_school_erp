<?php

use Illuminate\Support\Facades\Route;
use Modules\Homework\Http\Controllers\EvaluationController;
use Modules\Homework\Http\Controllers\HomeworkController;
use Modules\Homework\Http\Controllers\OverviewController;

Route::middleware('auth')->prefix('homework')->name('homework.')->group(function () {
    Route::resource('homeworks', HomeworkController::class)->except('show')->parameters(['homeworks' => 'homework']);

    Route::get('evaluate', [EvaluationController::class, 'index'])->name('evaluate.index');
    Route::post('evaluate', [EvaluationController::class, 'store'])->name('evaluate.store');

    Route::get('overview', [OverviewController::class, 'index'])->name('overview.index');
});
