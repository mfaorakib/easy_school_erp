<?php

namespace Modules\FrontOffice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FrontOffice\Models\ComplaintType;

class ComplaintTypeController extends Controller
{
    public function index()
    {
        $types = ComplaintType::orderBy('name')->get();

        return view('frontoffice::complaint-types.index', compact('types'));
    }

    public function create()
    {
        return view('frontoffice::complaint-types.form', ['type' => new ComplaintType]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        ComplaintType::create($data);

        return redirect()->route('frontoffice.complaintTypes.index')->with('status', 'Complaint type saved.');
    }

    public function edit(ComplaintType $complaintType)
    {
        return view('frontoffice::complaint-types.form', ['type' => $complaintType]);
    }

    public function update(Request $request, ComplaintType $complaintType)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $complaintType->update($data);

        return redirect()->route('frontoffice.complaintTypes.index')->with('status', 'Complaint type saved.');
    }

    public function destroy(ComplaintType $complaintType)
    {
        $complaintType->delete();

        return redirect()->route('frontoffice.complaintTypes.index')->with('status', 'Complaint type deleted.');
    }
}
