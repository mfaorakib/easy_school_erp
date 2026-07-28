<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\DashboardController;

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/dashboard/todos', [DashboardController::class, 'storeTodo'])->name('dashboard.todos.store');
    Route::post('/dashboard/todos/{todo}/toggle', [DashboardController::class, 'toggleTodo'])->name('dashboard.todos.toggle');
    Route::delete('/dashboard/todos/{todo}', [DashboardController::class, 'destroyTodo'])->name('dashboard.todos.destroy');
});
