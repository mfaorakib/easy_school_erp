<?php

use Illuminate\Support\Facades\Route;
use Modules\OnlineExam\Http\Controllers\OnlineExamController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('onlineexams', OnlineExamController::class)->names('onlineexam');
});
