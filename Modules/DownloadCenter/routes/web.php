<?php

use Illuminate\Support\Facades\Route;
use Modules\DownloadCenter\Http\Controllers\CenterController;
use Modules\DownloadCenter\Http\Controllers\ContentController;
use Modules\DownloadCenter\Http\Controllers\ContentTypeController;

Route::middleware('auth')->prefix('downloadcenter')->name('downloadcenter.')->group(function () {
    Route::get('center', [CenterController::class, 'index'])->name('center.index');
    Route::resource('contents', ContentController::class)->except('show')->parameters(['contents' => 'content']);
    Route::resource('types', ContentTypeController::class)->except('show')->parameters(['types' => 'type']);
});
