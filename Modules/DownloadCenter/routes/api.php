<?php

use Illuminate\Support\Facades\Route;
use Modules\DownloadCenter\Http\Controllers\DownloadCenterController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('downloadcenters', DownloadCenterController::class)->names('downloadcenter');
});
