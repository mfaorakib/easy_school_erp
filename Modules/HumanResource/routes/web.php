<?php

use Illuminate\Support\Facades\Route;
use Modules\HumanResource\Http\Controllers\DepartmentController;
use Modules\HumanResource\Http\Controllers\DesignationController;
use Modules\HumanResource\Http\Controllers\ResignationController;
use Modules\HumanResource\Http\Controllers\StaffController;

Route::middleware('auth')->prefix('hr')->name('hr.')->group(function () {
    Route::resource('staff', StaffController::class)->except('show')->parameters(['staff' => 'staff']);
    Route::get('staff/{staff}/profile', [StaffController::class, 'show'])
        ->middleware('permission:hr.view')->name('staff.show');
    Route::resource('designations', DesignationController::class)->except('show');
    Route::resource('departments', DepartmentController::class)->except('show');

    // Resignation applications — admin review (staff submit these from the Staff Portal)
    Route::get('resignations', [ResignationController::class, 'index'])->name('resignations.index');
    Route::post('resignations/{resignation}/review', [ResignationController::class, 'review'])->name('resignations.review');
});
