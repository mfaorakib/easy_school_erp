<?php

namespace Modules\GuardianPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\GuardianPortal\Services\GuardianPortalService;

/**
 * Guardian/student document listing for one child. As with every
 * specific-student lookup in this portal, access is gated through
 * GuardianPortalService::assertOwnsChild() before any data is read, so
 * neither a guardian nor a student can view another family's records by
 * guessing a student id in the URL.
 */
class PortalDocumentController extends Controller
{
    public function __construct(
        private readonly GuardianPortalService $portal,
    ) {}

    public function index(Request $request)
    {
        $student = $this->portal->assertOwnsChild($request->user(), (int) $request->route('student'));
        $documents = $student->documents()->with('type')->latest()->get();

        return view('guardianportal::documents', compact('student', 'documents'));
    }
}
