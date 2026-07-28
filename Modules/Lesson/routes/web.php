<?php

use Illuminate\Support\Facades\Route;
use Modules\Lesson\Http\Controllers\LessonController;
use Modules\Lesson\Http\Controllers\OverviewController;
use Modules\Lesson\Http\Controllers\TopicController;

Route::middleware('auth')->prefix('lesson')->name('lesson.')->group(function () {
    Route::resource('lessons', LessonController::class)->except('show')->parameters(['lessons' => 'lesson']);

    Route::get('topics', [TopicController::class, 'index'])->name('topics.index');
    Route::post('topics', [TopicController::class, 'store'])->name('topics.store');
    Route::post('topics/{topic}/toggle', [TopicController::class, 'toggle'])->name('topics.toggle');
    Route::delete('topics/{topic}', [TopicController::class, 'destroy'])->name('topics.destroy');

    Route::get('overview', [OverviewController::class, 'index'])->name('overview.index');
});
