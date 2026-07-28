<?php

use Illuminate\Support\Facades\Route;
use Modules\Behaviour\Http\Controllers\BehaviourController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('behaviours', BehaviourController::class)->names('behaviour');
});
