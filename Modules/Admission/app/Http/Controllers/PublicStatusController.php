<?php

namespace Modules\Admission\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Admission\Services\AdmissionService;

/** Public "check my admission application status" page. No auth. */
class PublicStatusController extends Controller
{
    public function show(): View
    {
        return view('admission::public.status');
    }

    public function lookup(Request $request, AdmissionService $svc): View
    {
        $request->validate([
            'reference_no' => 'required|string',
        ]);

        $application = $svc->findByReference($request->reference_no);

        return view('admission::public.status', [
            'application' => $application,
            'searched'    => $request->reference_no,
        ]);
    }
}
