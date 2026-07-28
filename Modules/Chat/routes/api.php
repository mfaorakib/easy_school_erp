<?php

use Illuminate\Support\Facades\Route;

// Chat API surface not used (server-rendered).
Route::middleware(["auth:sanctum"])->prefix("v1")->group(function () {
    //
});
