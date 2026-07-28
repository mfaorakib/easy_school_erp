<?php

use Illuminate\Support\Facades\Route;
use Modules\Transport\Http\Controllers\RouteController;
use Modules\Transport\Http\Controllers\RouteVehicleController;
use Modules\Transport\Http\Controllers\StudentTransportController;
use Modules\Transport\Http\Controllers\VehicleController;

Route::middleware('auth')->prefix('transport')->name('transport.')->group(function () {
    Route::resource('routes', RouteController::class)->except('show')->parameters(['routes' => 'route']);
    Route::resource('vehicles', VehicleController::class)->except('show')->parameters(['vehicles' => 'vehicle']);

    Route::get('assign-vehicles', [RouteVehicleController::class, 'index'])->name('assign-vehicles.index');
    Route::post('assign-vehicles', [RouteVehicleController::class, 'store'])->name('assign-vehicles.store');

    Route::get('students', [StudentTransportController::class, 'index'])->name('students.index');
    Route::post('students', [StudentTransportController::class, 'store'])->name('students.store');
    Route::post('students/unassign', [StudentTransportController::class, 'unassign'])->name('students.unassign');
});
