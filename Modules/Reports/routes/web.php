<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\AttendanceReportController;
use Modules\Reports\Http\Controllers\ExamReportController;
use Modules\Reports\Http\Controllers\FeeReportController;
use Modules\Reports\Http\Controllers\StudentReportController;
use Modules\Reports\Http\Controllers\WalletReportController;

Route::middleware('auth')->prefix('reports')->name('reports.')->group(function () {
    // --- People (Agent A) ---
    Route::get('students', [StudentReportController::class, 'students'])->name('students');
    Route::get('guardians', [StudentReportController::class, 'guardians'])->name('guardians');
    Route::get('attendance', [AttendanceReportController::class, 'index'])->name('attendance');

    // --- Financial (Agent B) ---
    Route::get('fees-collection', [FeeReportController::class, 'collection'])->name('fees.collection');
    Route::get('fees-due', [FeeReportController::class, 'due'])->name('fees.due');
    Route::get('wallet', [WalletReportController::class, 'index'])->name('wallet');

    // --- Academic (Agent C) ---
    Route::get('exam-results', [ExamReportController::class, 'results'])->name('exam.results');
    Route::get('merit-list', [ExamReportController::class, 'merit'])->name('exam.merit');
});
