<?php

use Illuminate\Support\Facades\Route;
use Modules\Library\Http\Controllers\BookCategoryController;
use Modules\Library\Http\Controllers\BookController;
use Modules\Library\Http\Controllers\IssueController;
use Modules\Library\Http\Controllers\LibraryMemberController;

Route::middleware('auth')->prefix('library')->name('library.')->group(function () {
    Route::resource('categories', BookCategoryController::class)->except('show')->parameters(['categories' => 'category']);
    Route::resource('books', BookController::class)->except('show')->parameters(['books' => 'book']);

    Route::get('members', [LibraryMemberController::class, 'index'])->name('members.index');
    Route::post('members', [LibraryMemberController::class, 'store'])->name('members.store');
    Route::delete('members/{member}', [LibraryMemberController::class, 'destroy'])->name('members.destroy');

    Route::get('issue', [IssueController::class, 'index'])->name('issue.index');
    Route::post('issue', [IssueController::class, 'store'])->name('issue.store');
    Route::post('issue/{issue}/return', [IssueController::class, 'returnBook'])->name('issue.return');
    Route::post('issue/{issue}/collect-fine', [IssueController::class, 'collectFine'])->name('issue.collect-fine');
    Route::get('issue/{issue}/fine-receipt', [IssueController::class, 'fineReceipt'])->name('issue.fine-receipt');
    Route::get('issue/history', [IssueController::class, 'history'])->name('issue.history');
});
