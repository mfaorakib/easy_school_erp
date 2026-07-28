<?php

use Illuminate\Support\Facades\Route;
use Modules\GuardianPortal\Http\Controllers\PortalAttendanceController;
use Modules\GuardianPortal\Http\Controllers\PortalDashboardController;
use Modules\GuardianPortal\Http\Controllers\PortalDocumentController;
use Modules\GuardianPortal\Http\Controllers\PortalLeaveController;
use Modules\GuardianPortal\Http\Controllers\PortalNoticeController;
use Modules\GuardianPortal\Http\Controllers\PortalPaymentController;

/*
 * Guardian + student portal. role:parent|student means a staff/admin account
 * can never land here even if they guess the URL — see LoginController for
 * the post-login redirect and bootstrap/app.php for the `role` middleware
 * alias. GuardianPortalService::children() resolves either a guardian's own
 * children or a student's own record, so every controller below works for
 * both roles without checking which one is signed in.
 */
Route::middleware(['auth', 'role:parent|student'])->prefix('portal')->name('portal.')->group(function () {
    // Dashboard + per-child fee view (Agent 3)
    Route::get('/', [PortalDashboardController::class, 'index'])->name('dashboard');
    Route::get('children/{student}', [PortalDashboardController::class, 'child'])->name('children.show');

    // Pay Now + return/manual confirmation + history + receipt (Agent 4)
    Route::post('pay/start', [PortalPaymentController::class, 'start'])->name('pay.start');
    Route::get('pay/return/{token}', [PortalPaymentController::class, 'return'])->name('pay.return');
    Route::get('pay/manual/{token}', [PortalPaymentController::class, 'manualForm'])->name('pay.manual');
    Route::post('pay/manual/{token}', [PortalPaymentController::class, 'manualSubmit'])->name('pay.manual.submit');

    Route::get('payments', [PortalPaymentController::class, 'history'])->name('payments.index');
    Route::get('payments/{payment}/receipt', [PortalPaymentController::class, 'receipt'])->name('payments.receipt');

    // Attendance history for one child (own attendance, if a student session)
    Route::get('children/{student}/attendance', [PortalAttendanceController::class, 'index'])->name('children.attendance');

    // Leave/absence request for one child — list own requests + submit a new one
    Route::get('children/{student}/leave', [PortalLeaveController::class, 'index'])->name('children.leave.index');
    Route::post('children/{student}/leave', [PortalLeaveController::class, 'store'])->name('children.leave.store');

    // Notices relevant to this session's role (parent/student), portal-themed
    Route::get('notices', [PortalNoticeController::class, 'index'])->name('notices.index');

    // Uploaded documents for one child (birth certificate, transfer certificate, ...)
    Route::get('children/{student}/documents', [PortalDocumentController::class, 'index'])->name('children.documents');
});
