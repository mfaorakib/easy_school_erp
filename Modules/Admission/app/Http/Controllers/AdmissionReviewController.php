<?php

namespace Modules\Admission\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admission\Models\AdmissionApplication;
use Modules\Admission\Services\AdmissionService;

class AdmissionReviewController extends Controller
{
    public function confirm(Request $request, AdmissionApplication $application, AdmissionService $svc)
    {
        $data = $request->validate([
            'class_id'   => ['nullable', 'integer', 'exists:classes,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'photo'      => ['nullable', 'image', 'max:2048'],
        ]);

        $placement = [
            'class_id'   => $data['class_id'] ?? null,
            'section_id' => $data['section_id'],
        ];

        if ($request->hasFile('photo')) {
            $placement['photo'] = $request->file('photo')->store('admission', 'public');
        }

        $svc->confirm($application, $placement, $request->user());

        return redirect()
            ->route('admission.admin.applications.show', $application)
            ->with('status', 'Application confirmed — student enrolled.');
    }

    public function reject(Request $request, AdmissionApplication $application, AdmissionService $svc)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $svc->reject($application, $data['reason'], $request->user());

        return redirect()
            ->route('admission.admin.applications.show', $application)
            ->with('status', 'Application rejected.');
    }
}
