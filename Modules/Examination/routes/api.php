<?php

use Illuminate\Support\Facades\Route;
use Modules\Examination\Http\Controllers\ExaminationController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('examinations', ExaminationController::class)->names('examination');
});
