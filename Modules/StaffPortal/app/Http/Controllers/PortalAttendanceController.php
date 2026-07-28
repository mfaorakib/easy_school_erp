<?php

namespace Modules\StaffPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Models\StaffAttendance;
use Modules\StaffPortal\Services\StaffPortalService;

/**
 * A staff member's own attendance history, scoped to the logged-in user's
 * own Staff record via StaffPortalService::assertStaff() first — a staff
 * member never sees anyone else's attendance rows through this controller.
 */
class PortalAttendanceController extends Controller
{
    public function __construct(
        private readonly StaffPortalService $portal,
    ) {}

    public function index(Request $request)
    {
        $staff = $this->portal->assertStaff($request->user());

        $from = $request->filled('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse($request->query('to'))->startOfDay()
            : Carbon::now()->startOfDay();

        $records = StaffAttendance::where('staff_id', $staff->id)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')
            ->get();

        $summary = collect(AttendanceStatus::cases())
            ->map(fn (AttendanceStatus $status) => [
                'status' => $status,
                'label'  => $status->label(),
                'count'  => $records->filter(fn ($r) => $r->status === $status)->count(),
            ])
            ->filter(fn (array $row) => $row['count'] > 0)
            ->values();

        return view('staffportal::attendance', compact('staff', 'records', 'summary', 'from', 'to'));
    }
}
