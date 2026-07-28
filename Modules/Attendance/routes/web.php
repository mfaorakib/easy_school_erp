<?php

use Illuminate\Support\Facades\Route;
use Modules\Attendance\Http\Controllers\StaffAttendanceController;
use Modules\Attendance\Http\Controllers\StudentAttendanceController;

Route::middleware('auth')->prefix('attendance')->name('attendance.')->group(function () {
    Route::get('student', [StudentAttendanceController::class, 'index'])->name('student.index');
    Route::post('student', [StudentAttendanceController::class, 'store'])->name('student.store');

    Route::get('staff', [StaffAttendanceController::class, 'index'])->name('staff.index');
    Route::post('staff', [StaffAttendanceController::class, 'store'])->name('staff.store');
});
