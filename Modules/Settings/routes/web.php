<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\AppearanceController;
use Modules\Settings\Http\Controllers\EmailSettingController;
use Modules\Settings\Http\Controllers\GeneralSettingController;
use Modules\Settings\Http\Controllers\HolidayController;
use Modules\Settings\Http\Controllers\LocalizationController;
use Modules\Settings\Http\Controllers\PaymentMethodController;
use Modules\Settings\Http\Controllers\SidebarManagerController;
use Modules\Settings\Http\Controllers\SmsSettingController;

Route::middleware('auth')->prefix('settings')->name('settings.')->group(function () {
    // General (Agent A)
    Route::get('/', [GeneralSettingController::class, 'edit'])->name('index');
    Route::get('general', [GeneralSettingController::class, 'edit'])->name('general');
    Route::put('general', [GeneralSettingController::class, 'update'])->name('general.update');

    // Localization (Agent B)
    Route::get('localization', [LocalizationController::class, 'edit'])->name('localization');
    Route::put('localization', [LocalizationController::class, 'update'])->name('localization.update');

    // Email + SMS (Agent C)
    Route::get('email', [EmailSettingController::class, 'edit'])->name('email');
    Route::put('email', [EmailSettingController::class, 'update'])->name('email.update');
    Route::get('sms', [SmsSettingController::class, 'edit'])->name('sms');
    Route::put('sms', [SmsSettingController::class, 'update'])->name('sms.update');

    // Appearance (Agent E)
    Route::get('appearance', [AppearanceController::class, 'edit'])->name('appearance');
    Route::put('appearance', [AppearanceController::class, 'update'])->name('appearance.update');

    // Holidays (Agent D)
    Route::resource('holidays', HolidayController::class)->except('show')->parameters(['holidays' => 'holiday']);

    // Payment methods (Agent E)
    Route::resource('payment-methods', PaymentMethodController::class)->except('show')
        ->parameters(['payment-methods' => 'paymentMethod'])->names('paymentMethods');

    // Sidebar Manager — reorder the admin sidebar's sections and each section's sub-groups
    Route::get('sidebar-manager', [SidebarManagerController::class, 'edit'])->name('sidebarManager.edit');
    Route::post('sidebar-manager/sections', [SidebarManagerController::class, 'updateSections'])->name('sidebarManager.sections.update');
    Route::post('sidebar-manager/groups', [SidebarManagerController::class, 'updateGroups'])->name('sidebarManager.groups.update');
    Route::post('sidebar-manager/reset', [SidebarManagerController::class, 'reset'])->name('sidebarManager.reset');

    // Custom sections + moving a sub-group to a (possibly different) section
    Route::post('sidebar-manager/sections/create', [SidebarManagerController::class, 'storeSection'])->name('sidebarManager.sections.store');
    Route::delete('sidebar-manager/sections/{section}', [SidebarManagerController::class, 'destroySection'])->name('sidebarManager.sections.destroy');
    Route::post('sidebar-manager/groups/assign', [SidebarManagerController::class, 'assignGroup'])->name('sidebarManager.groups.assign');
    Route::post('sidebar-manager/links', [SidebarManagerController::class, 'updateLinks'])->name('sidebarManager.links.update');
    Route::post('sidebar-manager/hidden/toggle', [SidebarManagerController::class, 'toggleHidden'])->name('sidebarManager.hidden.toggle');
});
