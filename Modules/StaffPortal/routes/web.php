<?php

use Illuminate\Support\Facades\Route;
use Modules\StaffPortal\Http\Controllers\PortalAdvanceController;
use Modules\StaffPortal\Http\Controllers\PortalAttendanceController;
use Modules\StaffPortal\Http\Controllers\PortalDashboardController;
use Modules\StaffPortal\Http\Controllers\PortalPayslipController;
use Modules\StaffPortal\Http\Controllers\PortalResignationController;

/*
 * Any authenticated user with a linked Staff record can reach this — not
 * role-restricted (Teacher/Accountant/Librarian/... all have one). This is
 * ADDITIVE: it doesn't replace a staff member's normal admin-panel access,
 * it's a focused "everything about me" hub reachable via a topbar link,
 * same idea as GuardianPortal but for employment data instead of a family's.
 */
Route::middleware(['auth'])->prefix('staff-portal')->name('staffportal.')->group(function () {
    Route::get('/', [PortalDashboardController::class, 'index'])->name('dashboard');

    Route::get('payslips', [PortalPayslipController::class, 'index'])->name('payslips.index');
    Route::get('payslips/{payslip}', [PortalPayslipController::class, 'show'])->name('payslips.show');

    Route::get('attendance', [PortalAttendanceController::class, 'index'])->name('attendance.index');

    Route::get('resignation', [PortalResignationController::class, 'index'])->name('resignation.index');
    Route::get('resignation/create', [PortalResignationController::class, 'create'])->name('resignation.create');
    Route::post('resignation', [PortalResignationController::class, 'store'])->name('resignation.store');

    Route::get('advances', [PortalAdvanceController::class, 'index'])->name('advances.index');
    Route::get('advances/create', [PortalAdvanceController::class, 'create'])->name('advances.create');
    Route::post('advances', [PortalAdvanceController::class, 'store'])->name('advances.store');
});
