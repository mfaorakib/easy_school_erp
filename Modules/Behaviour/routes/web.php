<?php

use Illuminate\Support\Facades\Route;
use Modules\Behaviour\Http\Controllers\BehaviourTypeController;
use Modules\Behaviour\Http\Controllers\RecordController;
use Modules\Behaviour\Http\Controllers\ReportController;

Route::middleware('auth')->prefix('behaviour')->name('behaviour.')->group(function () {
    Route::resource('types', BehaviourTypeController::class)->except('show')->parameters(['types' => 'type']);

    Route::get('records', [RecordController::class, 'index'])->name('records.index');
    Route::post('records', [RecordController::class, 'store'])->name('records.store');
    Route::delete('records/{record}', [RecordController::class, 'destroy'])->name('records.destroy');

    Route::get('report', [ReportController::class, 'index'])->name('report.index');
});
