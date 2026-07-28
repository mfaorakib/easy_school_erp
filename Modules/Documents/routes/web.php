<?php

use Illuminate\Support\Facades\Route;
use Modules\Documents\Http\Controllers\CertificateController;
use Modules\Documents\Http\Controllers\CertificateTemplateController;
use Modules\Documents\Http\Controllers\IdCardController;
use Modules\Documents\Http\Controllers\IdCardTemplateController;

Route::middleware('auth')->prefix('documents')->name('documents.')->group(function () {
    // Templates (Agent A + B)
    Route::resource('id-card-templates', IdCardTemplateController::class)->except('show')
        ->parameters(['id-card-templates' => 'idCardTemplate'])->names('idCardTemplates');
    // Registered BEFORE the resource below: a literal segment sharing a
    // prefix with a resource's {param} route MUST come first, or the
    // resource's wildcard greedily matches "preview" as the id and 404s via
    // failed model binding (see easyschool-staff-portal-done.md for the same
    // class of bug). POST *and* PUT: the Preview button lives inside the
    // same <form> as the real Update action, so on an edit page it carries
    // that form's spoofed _method=PUT hidden field too — Laravel reinterprets
    // the submission as PUT before routing, regardless of which button was
    // actually clicked.
    Route::match(['post', 'put'], 'certificate-templates/preview', [CertificateTemplateController::class, 'preview'])->name('certificateTemplates.preview');
    Route::resource('certificate-templates', CertificateTemplateController::class)->except('show')
        ->parameters(['certificate-templates' => 'certificateTemplate'])->names('certificateTemplates');

    // ID card generation (Agent C)
    Route::get('id-cards', [IdCardController::class, 'index'])->name('idCards.index');
    Route::post('id-cards/generate', [IdCardController::class, 'generate'])->name('idCards.generate');

    // Certificate generation (Agent D)
    Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
    Route::post('certificates/generate', [CertificateController::class, 'generate'])->name('certificates.generate');
});
