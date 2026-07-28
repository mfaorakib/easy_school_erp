<?php

use Illuminate\Support\Facades\Route;
use Modules\FrontOffice\Http\Controllers\CallLogController;
use Modules\FrontOffice\Http\Controllers\ComplaintController;
use Modules\FrontOffice\Http\Controllers\ComplaintTypeController;
use Modules\FrontOffice\Http\Controllers\EnquiryController;
use Modules\FrontOffice\Http\Controllers\PostalController;
use Modules\FrontOffice\Http\Controllers\VisitorController;

Route::middleware('auth')->prefix('front-office')->name('frontoffice.')->group(function () {
    // Admission enquiries + follow-ups (Agent A)
    Route::resource('enquiries', EnquiryController::class)->parameters(['enquiries' => 'enquiry']);
    Route::post('enquiries/{enquiry}/followups', [EnquiryController::class, 'addFollowup'])->name('enquiries.followups.add');

    // Visitor book (Agent B)
    Route::resource('visitors', VisitorController::class)->except('show')->parameters(['visitors' => 'visitor']);
    Route::post('visitors/{visitor}/checkout', [VisitorController::class, 'checkout'])->name('visitors.checkout');

    // Postal dispatch/receive (Agent C)
    Route::resource('postal', PostalController::class)->except('show')->parameters(['postal' => 'postal']);

    // Complaints + types (Agent D)
    Route::resource('complaint-types', ComplaintTypeController::class)->except('show')
        ->parameters(['complaint-types' => 'complaintType'])->names('complaintTypes');
    Route::resource('complaints', ComplaintController::class)->except('show')->parameters(['complaints' => 'complaint']);

    // Phone call log (Agent E)
    Route::resource('call-logs', CallLogController::class)->except('show')
        ->parameters(['call-logs' => 'callLog'])->names('callLogs');
});
