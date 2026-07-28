<?php

use Illuminate\Support\Facades\Route;
use Modules\Homework\Http\Controllers\HomeworkController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('homework', HomeworkController::class)->names('homework');
});
