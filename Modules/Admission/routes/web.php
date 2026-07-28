<?php

use Illuminate\Support\Facades\Route;
use Modules\Admission\Http\Controllers\AdminApplicationController;
use Modules\Admission\Http\Controllers\AdmissionReviewController;
use Modules\Admission\Http\Controllers\AdmissionSettingController;
use Modules\Admission\Http\Controllers\PublicApplyController;
use Modules\Admission\Http\Controllers\PublicStatusController;

/*
 * Public admission form (no auth).
 */
Route::prefix('admission')->name('admission.')->group(function () {
    // Apply (Agent A)
    Route::get('apply', [PublicApplyController::class, 'create'])->name('apply');
    Route::post('apply', [PublicApplyController::class, 'store'])->name('apply.store');
    Route::get('applied/{application:reference_no}', [PublicApplyController::class, 'applied'])->name('applied');

    // Status check (Agent B)
    Route::get('status', [PublicStatusController::class, 'show'])->name('status');
    Route::post('status', [PublicStatusController::class, 'lookup'])->name('status.lookup');
});

/*
 * Admin review + settings (auth).
 */
Route::middleware('auth')->prefix('admission-admin')->name('admission.admin.')->group(function () {
    // Applications list + detail (Agent C)
    Route::get('applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::get('applications/{application}', [AdminApplicationController::class, 'show'])->name('applications.show');

    // Confirm / reject actions (Agent D) — posted from Agent C's show page
    Route::post('applications/{application}/confirm', [AdmissionReviewController::class, 'confirm'])->name('applications.confirm');
    Route::post('applications/{application}/reject', [AdmissionReviewController::class, 'reject'])->name('applications.reject');

    // ID-format settings (Agent E)
    Route::get('settings', [AdmissionSettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [AdmissionSettingController::class, 'update'])->name('settings.update');
});
