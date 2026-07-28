<?php

namespace Modules\Leave\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HumanResource\Models\Staff;
use Modules\Leave\Models\Shift;
use Modules\Leave\Models\StaffShift;
use Modules\Leave\Services\LeaveService;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::orderBy('start_time')->get();

        return view('leave::shifts.index', compact('shifts'));
    }

    public function create()
    {
        return view('leave::shifts.form', ['shift' => new Shift]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'start_time' => ['required'],
            'end_time'   => ['required'],
        ]);

        Shift::create($request->only('name', 'start_time', 'end_time') + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('leave.shifts.index')->with('status', 'Shift saved.');
    }

    public function edit(Shift $shift)
    {
        return view('leave::shifts.form', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'start_time' => ['required'],
            'end_time'   => ['required'],
        ]);

        $shift->update($request->only('name', 'start_time', 'end_time') + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('leave.shifts.index')->with('status', 'Shift saved.');
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();

        return redirect()->route('leave.shifts.index')->with('status', 'Shift saved.');
    }

    public function assignIndex()
    {
        $staff = Staff::where('is_active', true)->orderBy('full_name')->get();
        $shifts = Shift::active()->orderBy('start_time')->get();
        $assigned = StaffShift::with('shift')->get()->keyBy('staff_id');

        return view('leave::shifts.assign', compact('staff', 'shifts', 'assigned'));
    }

    public function assign(Request $request, LeaveService $leave)
    {
        $request->validate([
            'staff_id' => ['required', 'exists:staff,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
        ]);

        $leave->assignShift((int) $request->staff_id, (int) $request->shift_id);

        return redirect()->route('leave.shifts.assign')->with('status', 'Shift assigned.');
    }
}
