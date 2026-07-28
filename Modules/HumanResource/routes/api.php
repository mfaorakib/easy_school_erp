<?php

use Illuminate\Support\Facades\Route;
use Modules\HumanResource\Http\Controllers\HumanResourceController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('humanresources', HumanResourceController::class)->names('humanresource');
});
