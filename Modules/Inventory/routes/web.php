<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\IssueController;
use Modules\Inventory\Http\Controllers\ItemCategoryController;
use Modules\Inventory\Http\Controllers\ItemController;
use Modules\Inventory\Http\Controllers\ItemStoreController;
use Modules\Inventory\Http\Controllers\ReceiveController;
use Modules\Inventory\Http\Controllers\SellController;
use Modules\Inventory\Http\Controllers\SupplierController;

Route::middleware('auth')->prefix('inventory')->name('inventory.')->group(function () {
    Route::resource('categories', ItemCategoryController::class)->except('show')->parameters(['categories' => 'category']);
    Route::resource('suppliers', SupplierController::class)->except('show')->parameters(['suppliers' => 'supplier']);
    Route::resource('stores', ItemStoreController::class)->except('show')->parameters(['stores' => 'store']);
    Route::resource('items', ItemController::class)->except('show')->parameters(['items' => 'item']);

    Route::get('receive', [ReceiveController::class, 'index'])->name('receive.index');
    Route::post('receive', [ReceiveController::class, 'store'])->name('receive.store');
    Route::get('receive/{receive}/voucher', [ReceiveController::class, 'voucher'])->name('receive.voucher');
    Route::post('receive/{receive}/adjust', [ReceiveController::class, 'adjust'])->name('receive.adjust');

    Route::get('issue', [IssueController::class, 'index'])->name('issue.index');
    Route::post('issue', [IssueController::class, 'store'])->name('issue.store');
    Route::post('issue/{issue}/return', [IssueController::class, 'returnItem'])->name('issue.return');

    Route::get('sell', [SellController::class, 'index'])->name('sell.index');
    Route::post('sell', [SellController::class, 'store'])->name('sell.store');
    Route::get('sell/{sell}/voucher', [SellController::class, 'voucher'])->name('sell.voucher');
    Route::post('sell/{sell}/adjust', [SellController::class, 'adjust'])->name('sell.adjust');
});
