<?php

namespace Modules\Leave\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HumanResource\Models\Staff;
use Modules\Leave\Models\LeaveApplication;
use Modules\Leave\Models\LeaveType;
use Modules\Leave\Services\LeaveService;

class ApplicationController extends Controller
{
    public function index()
    {
        $applications = LeaveApplication::with(['staff', 'type'])->latest('id')->get();

        return view('leave::applications.index', compact('applications'));
    }

    public function create()
    {
        return view('leave::applications.form', [
            'staff' => Staff::where('is_active', true)->orderBy('full_name')->get(),
            'types' => LeaveType::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, LeaveService $leave)
    {
        $request->validate([
            'staff_id'      => ['required', 'exists:staff,id'],
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'from_date'     => ['required', 'date'],
            'to_date'       => ['required', 'date', 'after_or_equal:from_date'],
            'reason'        => ['nullable', 'string', 'max:500'],
        ]);

        $leave->apply($request->only('staff_id', 'leave_type_id', 'from_date', 'to_date', 'reason'));

        return redirect()->route('leave.applications.index')->with('status', 'Leave application submitted.');
    }

    public function destroy(LeaveApplication $application)
    {
        $application->delete();

        return redirect()->route('leave.applications.index')->with('status', 'Application deleted.');
    }
}
