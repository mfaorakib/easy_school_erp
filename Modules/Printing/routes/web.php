<?php

use Illuminate\Support\Facades\Route;
use Modules\Printing\Http\Controllers\InvoiceController;
use Modules\Printing\Http\Controllers\MarksheetController;
use Modules\Printing\Http\Controllers\PrintCenterController;
use Modules\Printing\Http\Controllers\ProgressCardController;
use Modules\Printing\Http\Controllers\SeatPlanPrintController;
use Modules\Printing\Http\Controllers\TabulationController;

Route::middleware('auth')->prefix('printing')->name('printing.')->group(function () {
    Route::get('/', [PrintCenterController::class, 'index'])->name('index');

    // Marksheet (Agent A)
    Route::get('marksheet', [MarksheetController::class, 'index'])->name('marksheet');
    Route::post('marksheet', [MarksheetController::class, 'generate'])->name('marksheet.generate');

    // Tabulation (Agent B)
    Route::get('tabulation', [TabulationController::class, 'index'])->name('tabulation');
    Route::post('tabulation', [TabulationController::class, 'generate'])->name('tabulation.generate');

    // Progress card (Agent C)
    Route::get('progress-card', [ProgressCardController::class, 'index'])->name('progress');
    Route::post('progress-card', [ProgressCardController::class, 'generate'])->name('progress.generate');

    // Fee statement / invoice (Agent D)
    Route::get('invoice', [InvoiceController::class, 'index'])->name('invoice');
    Route::post('invoice', [InvoiceController::class, 'generate'])->name('invoice.generate');

    // Exam seat plan (Agent B)
    Route::get('seat-plan', [SeatPlanPrintController::class, 'index'])->name('seat_plan');
    Route::get('seat-plan/{exam}/room/{classroom}', [SeatPlanPrintController::class, 'room'])->name('seat_plan.room');
    Route::get('seat-plan/{exam}/all', [SeatPlanPrintController::class, 'all'])->name('seat_plan.all');
});
