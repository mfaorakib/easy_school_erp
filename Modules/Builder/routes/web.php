<?php

use Illuminate\Support\Facades\Route;
use Modules\Builder\Http\Controllers\BlockController;
use Modules\Builder\Http\Controllers\ContactController;
use Modules\Builder\Http\Controllers\MenuController;
use Modules\Builder\Http\Controllers\MessageController;
use Modules\Builder\Http\Controllers\PageController;
use Modules\Builder\Http\Controllers\PreviewController;
use Modules\Builder\Http\Controllers\PublicController;
use Modules\Builder\Http\Controllers\SettingController;
use Modules\Builder\Http\Controllers\SliderController;
use Modules\Builder\Http\Controllers\TestimonialController;

/*
 * Public website (no auth) — the frontend the Builder composes.
 */
Route::get('/', [PublicController::class, 'home'])->name('builder.home');
Route::get('/p/{slug}', [PublicController::class, 'page'])->name('builder.page');
Route::post('/contact', [ContactController::class, 'submit'])->name('builder.contact.submit');

/*
 * Admin builder (auth).
 */
Route::middleware('auth')->prefix('builder')->name('builder.')->group(function () {
    // Pages
    Route::resource('pages', PageController::class)->except('show')->parameters(['pages' => 'page']);
    Route::post('pages/{page}/home', [PageController::class, 'makeHome'])->name('pages.home');
    Route::get('pages/{page}/preview', [PreviewController::class, 'show'])->name('pages.preview');

    // Section editor
    Route::get('pages/{page}/blocks', [BlockController::class, 'index'])->name('blocks.index');
    Route::post('pages/{page}/blocks', [BlockController::class, 'store'])->name('blocks.store');
    Route::post('pages/{page}/blocks/reorder', [BlockController::class, 'reorder'])->name('blocks.reorder');
    Route::put('blocks/{block}', [BlockController::class, 'update'])->name('blocks.update');
    Route::post('blocks/{block}/move', [BlockController::class, 'move'])->name('blocks.move');
    Route::post('blocks/{block}/duplicate', [BlockController::class, 'duplicate'])->name('blocks.duplicate');
    Route::delete('blocks/{block}', [BlockController::class, 'destroy'])->name('blocks.destroy');

    // Menus + items
    Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
    Route::post('menus/{menu}/items', [MenuController::class, 'addItem'])->name('menus.items.add');
    Route::put('menu-items/{item}', [MenuController::class, 'updateItem'])->name('menus.items.update');
    Route::post('menu-items/{item}/move', [MenuController::class, 'moveItem'])->name('menus.items.move');
    Route::delete('menu-items/{item}', [MenuController::class, 'removeItem'])->name('menus.items.remove');

    // Sliders + Testimonials
    Route::resource('sliders', SliderController::class)->except('show');
    Route::resource('testimonials', TestimonialController::class)->except('show');

    // Contact / newsletter inbox
    Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
    Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

    // Site settings
    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
});
