<?php

use Illuminate\Support\Facades\Route;
use Modules\Wallet\Http\Controllers\HistoryController;
use Modules\Wallet\Http\Controllers\TransactionController;
use Modules\Wallet\Http\Controllers\WalletController;

Route::middleware('auth')->prefix('wallet')->name('wallet.')->group(function () {
    Route::get('wallets', [WalletController::class, 'index'])->name('wallets.index');
    Route::post('wallets', [WalletController::class, 'store'])->name('wallets.store');

    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('transactions', [TransactionController::class, 'store'])->name('transactions.store');

    Route::get('history', [HistoryController::class, 'index'])->name('history.index');
});
