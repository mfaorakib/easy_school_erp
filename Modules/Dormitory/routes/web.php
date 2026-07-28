<?php

use Illuminate\Support\Facades\Route;
use Modules\Dormitory\Http\Controllers\DormitoryController;
use Modules\Dormitory\Http\Controllers\RoomController;
use Modules\Dormitory\Http\Controllers\RoomTypeController;
use Modules\Dormitory\Http\Controllers\StudentDormitoryController;

Route::middleware('auth')->prefix('dormitory')->name('dormitory.')->group(function () {
    Route::resource('dormitories', DormitoryController::class)->except('show')->parameters(['dormitories' => 'dormitory']);
    Route::resource('room-types', RoomTypeController::class)->except('show')->parameters(['room-types' => 'roomType']);
    Route::resource('rooms', RoomController::class)->except('show')->parameters(['rooms' => 'room']);

    Route::get('students', [StudentDormitoryController::class, 'index'])->name('students.index');
    Route::post('students', [StudentDormitoryController::class, 'store'])->name('students.store');
    Route::post('students/unassign', [StudentDormitoryController::class, 'unassign'])->name('students.unassign');
});
