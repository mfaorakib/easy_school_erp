<?php

use Illuminate\Support\Facades\Route;
use Modules\AcademicCore\Http\Controllers\AcademicCoreController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('academiccores', AcademicCoreController::class)->names('academiccore');
});
