<?php

use Illuminate\Support\Facades\Route;
use Modules\Accounting\Http\Controllers\AccountHeadController;
use Modules\Accounting\Http\Controllers\BankAccountController;
use Modules\Accounting\Http\Controllers\ReportController;
use Modules\Accounting\Http\Controllers\TransactionController;
use Modules\Accounting\Http\Controllers\TransferController;

Route::middleware('auth')->prefix('accounting')->name('accounting.')->group(function () {
    // Heads + bank accounts (Agent A)
    Route::resource('heads', AccountHeadController::class)->except('show')->parameters(['heads' => 'head']);
    Route::resource('bank-accounts', BankAccountController::class)->except('show')
        ->parameters(['bank-accounts' => 'bankAccount'])->names('banks');

    // Income + expense transactions (Agent B) — the `type` query selects income/expense
    Route::resource('transactions', TransactionController::class)->except('show')
        ->parameters(['transactions' => 'transaction']);

    // Transfers + summary (Agent C)
    Route::get('transfers', [TransferController::class, 'index'])->name('transfers.index');
    Route::get('transfers/create', [TransferController::class, 'create'])->name('transfers.create');
    Route::post('transfers', [TransferController::class, 'store'])->name('transfers.store');
    Route::get('summary', [ReportController::class, 'summary'])->name('summary');
});
