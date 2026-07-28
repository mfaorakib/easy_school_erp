<?php

use Illuminate\Support\Facades\Route;
use Modules\Timetable\Http\Controllers\ClassroomController;
use Modules\Timetable\Http\Controllers\PeriodController;
use Modules\Timetable\Http\Controllers\RoutineController;
use Modules\Timetable\Http\Controllers\TimetableBuilderController;

Route::middleware('auth')->prefix('timetable')->name('timetable.')->group(function () {
    // Periods + rooms (Agent A)
    Route::resource('periods', PeriodController::class)->except('show')->parameters(['periods' => 'period']);
    Route::resource('classrooms', ClassroomController::class)->except('show')->parameters(['classrooms' => 'classroom']);

    // Grid builder (Agent B)
    Route::get('builder', [TimetableBuilderController::class, 'edit'])->name('builder');
    Route::put('builder', [TimetableBuilderController::class, 'update'])->name('builder.update');

    // Read views (Agent C)
    Route::get('routine', [RoutineController::class, 'classRoutine'])->name('routine');
    Route::get('teacher', [RoutineController::class, 'teacherRoutine'])->name('teacher');
});
