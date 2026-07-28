<?php

use Illuminate\Support\Facades\Route;
use Modules\Communication\Http\Controllers\EventController;
use Modules\Communication\Http\Controllers\NoticeBoardController;
use Modules\Communication\Http\Controllers\NoticeController;

Route::middleware('auth')->prefix('communication')->name('communication.')->group(function () {
    Route::get('board', [NoticeBoardController::class, 'index'])->name('board.index');
    Route::resource('notices', NoticeController::class)->except('show')->parameters(['notices' => 'notice']);
    Route::resource('events', EventController::class)->except('show')->parameters(['events' => 'event']);
});
