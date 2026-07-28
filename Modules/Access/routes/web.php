<?php

use Illuminate\Support\Facades\Route;
use Modules\Access\Http\Controllers\Auth\LoginController;
use Modules\Access\Http\Controllers\Auth\PasswordResetController;
use Modules\Access\Http\Controllers\ProfileController;
use Modules\Access\Http\Controllers\RoleController;
use Modules\Access\Http\Controllers\RolePermissionController;
use Modules\Access\Http\Controllers\UserRoleController;

// Guest
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store']);

    Route::get('forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Authenticated
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

// Role & permission management (Agents A & B)
Route::middleware('auth')->prefix('access')->name('access.')->group(function () {
    Route::resource('roles', RoleController::class)->except(['show', 'edit', 'update']);
    Route::get('roles/{role}/edit', [RolePermissionController::class, 'edit'])->name('roles.edit');
    Route::put('roles/{role}', [RolePermissionController::class, 'update'])->name('roles.update');

    // User → role assignment (Agent C)
    Route::get('users', [UserRoleController::class, 'index'])->name('users.index');
    Route::get('users/{user}/edit', [UserRoleController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserRoleController::class, 'update'])->name('users.update');
});
