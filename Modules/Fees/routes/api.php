<?php

use Illuminate\Support\Facades\Route;
use Modules\Fees\Http\Controllers\FeesController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('fees', FeesController::class)->names('fees');
});
