<?php

use Illuminate\Support\Facades\Route;
use Modules\Fees\Http\Controllers\FeeAdjustmentController;
use Modules\Fees\Http\Controllers\FeeAssignController;
use Modules\Fees\Http\Controllers\FeeCollectController;
use Modules\Fees\Http\Controllers\FeeDiscountController;
use Modules\Fees\Http\Controllers\FeeGroupController;
use Modules\Fees\Http\Controllers\FeeMasterController;
use Modules\Fees\Http\Controllers\FeePaymentIntentController;
use Modules\Fees\Http\Controllers\FeePaymentReceiptController;
use Modules\Fees\Http\Controllers\FeeRefundController;
use Modules\Fees\Http\Controllers\FeeTypeController;
use Modules\Fees\Http\Controllers\StudentFineController;

Route::middleware('auth')->prefix('fees')->name('fees.')->group(function () {
    Route::resource('groups', FeeGroupController::class)->except('show')->parameters(['groups' => 'group']);
    Route::resource('types', FeeTypeController::class)->except('show')->parameters(['types' => 'type']);
    Route::resource('discounts', FeeDiscountController::class)->except('show')->parameters(['discounts' => 'discount']);
    Route::resource('masters', FeeMasterController::class)->except('show')->parameters(['masters' => 'master']);

    Route::get('assign', [FeeAssignController::class, 'index'])->name('assign.index');
    Route::post('assign', [FeeAssignController::class, 'store'])->name('assign.store');

    Route::get('collect', [FeeCollectController::class, 'index'])->name('collect.index');
    Route::post('collect', [FeeCollectController::class, 'collect'])->name('collect.pay');

    // Discount / waiver / scholarship adjustments on one student's assignment (Agent 1)
    Route::post('assignments/{assignment}/discounts', [FeeAdjustmentController::class, 'attach'])->name('assignments.discounts.attach');
    Route::delete('assignments/{assignment}/discounts/{discount}', [FeeAdjustmentController::class, 'detach'])->name('assignments.discounts.detach');

    // Online-payment intents awaiting manual (cash/bank/unintegrated mobile banking) confirmation (Agent 5)
    Route::get('intents', [FeePaymentIntentController::class, 'index'])->name('intents.index');
    Route::post('intents/{intent}/confirm', [FeePaymentIntentController::class, 'confirm'])->name('intents.confirm');
    Route::post('intents/{intent}/reject', [FeePaymentIntentController::class, 'reject'])->name('intents.reject');

    // Printable receipt for any payment (Agent 5)
    Route::get('payments/{payment}/receipt', [FeePaymentReceiptController::class, 'show'])->name('payments.receipt');

    // Refund part or all of an already-collected payment — posts a linked accounting adjustment, never edits the payment itself.
    Route::post('payments/{payment}/refund', [FeeRefundController::class, 'store'])->name('payments.refund');

    // Ad-hoc, one-off student fines (disciplinary/other) — separate from the structured Fee Group/Type/Master/Assignment hierarchy.
    Route::get('fines', [StudentFineController::class, 'index'])->name('fines.index');
    Route::post('fines', [StudentFineController::class, 'store'])->name('fines.store');
    Route::post('fines/{fine}/collect', [StudentFineController::class, 'collect'])->name('fines.collect');
    Route::get('fines/{fine}/receipt', [StudentFineController::class, 'receipt'])->name('fines.receipt');
});
