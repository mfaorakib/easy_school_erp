<?php

namespace Modules\FrontOffice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\FrontOffice\Models\AdmissionEnquiry;
use Modules\FrontOffice\Services\FrontOfficeService;
use Modules\HumanResource\Models\Staff;

class EnquiryController extends Controller
{
    public function index()
    {
        $enquiries = AdmissionEnquiry::with(['schoolClass', 'assignee'])
            ->latest('enquiry_date')
            ->latest('id')
            ->get();

        return view('frontoffice::enquiries.index', compact('enquiries'));
    }

    public function create()
    {
        return view('frontoffice::enquiries.form', [
            'enquiry' => new AdmissionEnquiry,
        ] + $this->dropdowns());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        AdmissionEnquiry::create($data);

        return redirect()->route('frontoffice.enquiries.index')->with('status', 'Enquiry saved.');
    }

    public function show(AdmissionEnquiry $enquiry)
    {
        $enquiry->load(['schoolClass', 'assignee', 'followups.creator']);

        return view('frontoffice::enquiries.show', compact('enquiry'));
    }

    public function edit(AdmissionEnquiry $enquiry)
    {
        return view('frontoffice::enquiries.form', compact('enquiry') + $this->dropdowns());
    }

    public function update(Request $request, AdmissionEnquiry $enquiry)
    {
        $enquiry->update($this->validated($request));

        return redirect()->route('frontoffice.enquiries.index')->with('status', 'Enquiry saved.');
    }

    public function destroy(AdmissionEnquiry $enquiry)
    {
        $enquiry->delete();

        return redirect()->route('frontoffice.enquiries.index')->with('status', 'Enquiry deleted.');
    }

    public function addFollowup(Request $request, AdmissionEnquiry $enquiry, FrontOfficeService $frontOffice)
    {
        $request->validate([
            'follow_up_date'      => ['required', 'date'],
            'next_follow_up_date' => ['nullable', 'date'],
            'response'            => ['nullable', 'string'],
            'note'                => ['nullable', 'string', 'max:255'],
        ]);

        $frontOffice->addFollowup($enquiry, $request->only('follow_up_date', 'next_follow_up_date', 'response', 'note'));

        return redirect()->route('frontoffice.enquiries.show', $enquiry)->with('status', 'Follow-up added.');
    }

    private function dropdowns(): array
    {
        return [
            'classes' => SchoolClass::active()->orderBy('name')->get(),
            'staff'   => Staff::where('is_active', true)->orderBy('full_name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'phone'               => ['nullable', 'string', 'max:40'],
            'email'               => ['nullable', 'email'],
            'address'             => ['nullable', 'string', 'max:255'],
            'description'         => ['nullable', 'string'],
            'class_id'            => ['nullable', 'exists:classes,id'],
            'source'              => ['nullable', 'string', 'max:255'],
            'no_of_child'         => ['nullable', 'integer', 'min:1'],
            'enquiry_date'        => ['required', 'date'],
            'next_follow_up_date' => ['nullable', 'date'],
            'assigned_to'         => ['nullable', 'exists:staff,id'],
            'status'              => ['required', 'in:active,won,lost'],
            'note'                => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($data['no_of_child'])) {
            $data['no_of_child'] = 1;
        }

        return $data;
    }
}
