<?php

use Illuminate\Support\Facades\Route;
use Modules\Dormitory\Http\Controllers\DormitoryController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('dormitories', DormitoryController::class)->names('dormitory');
});
