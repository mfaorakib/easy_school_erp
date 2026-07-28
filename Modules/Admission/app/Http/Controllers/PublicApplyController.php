<?php

namespace Modules\Admission\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\AcademicCore\Models\DocumentType;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\Admission\Models\AdmissionApplication;
use Modules\Admission\Models\AdmissionApplicationDocument;
use Modules\Admission\Services\AdmissionService;
use Modules\Foundation\Models\BaseGroup;

/** Public "apply for admission" form. No auth. */
class PublicApplyController extends Controller
{
    public function create(): View
    {
        $classes = SchoolClass::active()->orderBy('name')->get();
        $genders = optional(BaseGroup::where('slug', 'gender')->first())->setups()->where('is_active', true)->get() ?? collect();
        $documentTypes = DocumentType::active()->get();

        return view('admission::public.apply', compact('classes', 'genders', 'documentTypes'));
    }

    public function store(Request $request, AdmissionService $svc): RedirectResponse
    {
        $documentTypes = DocumentType::active()->get();

        $rules = [
            'first_name'        => 'required|string|max:70',
            'last_name'         => 'nullable|string|max:70',
            'gender_id'         => 'nullable|integer|exists:base_setups,id',
            'date_of_birth'     => 'nullable|date',
            'desired_class_id'  => 'required|integer|exists:classes,id',
            'guardian_name'     => 'nullable|string|max:120',
            'guardian_relation' => 'nullable|string|max:50',
            'guardian_mobile'   => 'nullable|string|max:30',
            'guardian_email'    => 'nullable|email|max:120',
            'present_address'   => 'nullable|string|max:500',
            'previous_school'   => 'nullable|string|max:150',
            'photo'             => 'nullable|image|max:2048',
        ];

        // Every active document type gets its own file field — required ones
        // are enforced here, the same as any other required form field.
        foreach ($documentTypes as $type) {
            $rules["documents.$type->id"] = [
                $type->is_required ? 'required' : 'nullable',
                'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120',
            ];
        }

        $data = $request->validate($rules);
        $documentUploads = $data['documents'] ?? [];
        unset($data['documents']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('admission', 'public');
        }

        unset($data['photo']);

        $application = $svc->apply($data);

        // Staged against the PENDING application, not a real student yet —
        // AdmissionService::confirm() copies these over once (and only if)
        // the application is actually approved.
        foreach ($documentUploads as $typeId => $file) {
            if (! $file) {
                continue;
            }

            AdmissionApplicationDocument::create([
                'admission_application_id' => $application->id,
                'document_type_id'         => $typeId,
                'file_path'                => $file->store('admission/documents', 'public'),
            ]);
        }

        return redirect()->route('admission.applied', $application->reference_no);
    }

    public function applied(AdmissionApplication $application): View
    {
        return view('admission::public.applied', compact('application'));
    }
}
