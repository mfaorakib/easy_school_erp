<?php

use Illuminate\Support\Facades\Route;
use Modules\Leave\Http\Controllers\ApplicationController;
use Modules\Leave\Http\Controllers\ApprovalController;
use Modules\Leave\Http\Controllers\EvaluationController;
use Modules\Leave\Http\Controllers\LeaveTypeController;
use Modules\Leave\Http\Controllers\ShiftController;
use Modules\Leave\Http\Controllers\StudentLeaveController;

Route::middleware('auth')->prefix('leave')->name('leave.')->group(function () {
    // Leave types (Agent A)
    Route::resource('types', LeaveTypeController::class)->except('show')->parameters(['types' => 'type']);

    // Applications (Agent B)
    Route::get('applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('applications/create', [ApplicationController::class, 'create'])->name('applications.create');
    Route::post('applications', [ApplicationController::class, 'store'])->name('applications.store');
    Route::delete('applications/{application}', [ApplicationController::class, 'destroy'])->name('applications.destroy');

    // Approvals + balance (Agent C)
    Route::get('approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::post('approvals/{application}/review', [ApprovalController::class, 'review'])->name('approvals.review');
    Route::get('balance', [ApprovalController::class, 'balance'])->name('balance');

    // Teacher evaluation (Agent D)
    Route::resource('evaluations', EvaluationController::class)->except('show')->parameters(['evaluations' => 'evaluation']);

    // Shifts + assignment (Agent E)
    Route::resource('shifts', ShiftController::class)->except('show')->parameters(['shifts' => 'shift']);
    Route::get('assign-shifts', [ShiftController::class, 'assignIndex'])->name('shifts.assign');
    Route::post('assign-shifts', [ShiftController::class, 'assign'])->name('shifts.assign.store');

    // Student leave/absence requests — admin review (separate from staff leave above)
    Route::get('student-applications', [StudentLeaveController::class, 'index'])->name('student.index');
    Route::post('student-applications/{application}/review', [StudentLeaveController::class, 'review'])->name('student.review');
});
