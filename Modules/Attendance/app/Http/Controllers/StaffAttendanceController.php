<?php

namespace Modules\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Services\AttendanceService;

class StaffAttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $service) {}

    public function index(Request $request)
    {
        $date = $request->date('date')?->toDateString() ?? now()->toDateString();

        return view('attendance::staff.index', [
            'statuses' => AttendanceStatus::markable(),
            'date'     => $date,
            'roster'   => $this->service->staffRoster($date),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date'     => ['required', 'date'],
            'status'   => ['required', 'array', 'min:1'],
            'status.*' => ['required', 'in:P,L,A,F'],
            'note'     => ['array'],
        ]);

        $count = $this->service->saveStaffAttendance($data['date'], $data['status'], $request->input('note', []));

        return redirect()
            ->route('attendance.staff.index', ['date' => $data['date']])
            ->with('status', "Attendance saved for {$count} staff.");
    }
}
