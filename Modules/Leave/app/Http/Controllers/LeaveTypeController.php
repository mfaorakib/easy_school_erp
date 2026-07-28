<?php

namespace Modules\Leave\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Leave\Models\LeaveType;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $types = LeaveType::orderBy('name')->get();

        return view('leave::types.index', compact('types'));
    }

    public function create()
    {
        return view('leave::types.form', ['type' => new LeaveType]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');

        LeaveType::create($data);

        return redirect()->route('leave.types.index')->with('status', 'Leave type saved.');
    }

    public function edit(LeaveType $type)
    {
        return view('leave::types.form', compact('type'));
    }

    public function update(Request $request, LeaveType $type)
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');

        $type->update($data);

        return redirect()->route('leave.types.index')->with('status', 'Leave type saved.');
    }

    public function destroy(LeaveType $type)
    {
        $type->delete();

        return redirect()->route('leave.types.index')->with('status', 'Leave type saved.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'days_allowed' => ['required', 'integer', 'min:0'],
        ]);
    }
}
