<?php

namespace Modules\StaffPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HumanResource\Models\ResignationApplication;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Models\SalaryAdvance;
use Modules\Attendance\Models\StaffAttendance;
use Modules\StaffPortal\Services\StaffPortalService;

/**
 * Landing page of the staff self-service portal: a lightweight "everything
 * about me at a glance" summary — latest payslip, this month's attendance
 * count, and whether a resignation/advance request is currently pending.
 */
class PortalDashboardController extends Controller
{
    public function __construct(private readonly StaffPortalService $portal) {}

    public function index(Request $request)
    {
        $staff = $this->portal->assertStaff($request->user());

        $latestPayslip = Payslip::where('staff_id', $staff->id)->latest('id')->first();

        $attendanceCount = StaffAttendance::where('staff_id', $staff->id)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->count();

        $hasPendingResignation = ResignationApplication::where('staff_id', $staff->id)
            ->where('status', 'pending')
            ->exists();

        $hasPendingAdvance = SalaryAdvance::where('staff_id', $staff->id)
            ->where('status', 'pending')
            ->exists();

        return view('staffportal::dashboard', compact(
            'staff', 'latestPayslip', 'attendanceCount', 'hasPendingResignation', 'hasPendingAdvance'
        ));
    }
}
