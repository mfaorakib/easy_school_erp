<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

// The site root ('/') is the public website, served by the Builder module
// (Modules\Builder\routes\web.php → PublicController@home).

// Language switcher (available to everyone).
Route::get('locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// /dashboard is served by the Dashboard module (Modules\Dashboard\routes\web.php).
