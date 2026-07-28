<?php

use Illuminate\Support\Facades\Route;

// Reports is a server-rendered, read-only module — no API surface.
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    //
});
