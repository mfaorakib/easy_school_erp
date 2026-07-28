<?php

namespace Modules\FrontOffice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FrontOffice\Models\Complaint;
use Modules\FrontOffice\Models\ComplaintType;
use Modules\HumanResource\Models\Staff;

class ComplaintController extends Controller
{
    public function index()
    {
        $complaints = Complaint::with(['type', 'assignee'])->latest('complaint_date')->latest('id')->get();

        return view('frontoffice::complaints.index', compact('complaints'));
    }

    public function create()
    {
        return view('frontoffice::complaints.form', ['complaint' => new Complaint] + $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Complaint::create($data);

        return redirect()->route('frontoffice.complaints.index')->with('status', 'Complaint saved.');
    }

    public function edit(Complaint $complaint)
    {
        return view('frontoffice::complaints.form', compact('complaint') + $this->formData());
    }

    public function update(Request $request, Complaint $complaint)
    {
        $data = $this->validated($request);

        $complaint->update($data);

        return redirect()->route('frontoffice.complaints.index')->with('status', 'Complaint saved.');
    }

    public function destroy(Complaint $complaint)
    {
        $complaint->delete();

        return redirect()->route('frontoffice.complaints.index')->with('status', 'Complaint deleted.');
    }

    private function formData(): array
    {
        return [
            'types' => ComplaintType::active()->orderBy('name')->get(),
            'staff' => Staff::where('is_active', true)->orderBy('full_name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        $request->validate([
            'complaint_type_id' => ['nullable', 'exists:complaint_types,id'],
            'complainant_name'  => ['required', 'string', 'max:255'],
            'phone'             => ['nullable', 'string', 'max:40'],
            'source'            => ['nullable', 'string', 'max:255'],
            'complaint_date'    => ['required', 'date'],
            'description'       => ['nullable', 'string'],
            'action_taken'      => ['nullable', 'string'],
            'assigned_to'       => ['nullable', 'exists:staff,id'],
            'status'            => ['required', 'in:open,in_progress,resolved'],
            'attachment'        => ['nullable', 'file', 'max:4096'],
        ]);

        $data = $request->only(
            'complaint_type_id',
            'complainant_name',
            'phone',
            'source',
            'complaint_date',
            'description',
            'action_taken',
            'assigned_to',
            'status'
        );

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('frontoffice', 'public');
        }

        return $data;
    }
}
