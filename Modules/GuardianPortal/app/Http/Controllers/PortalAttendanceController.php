<?php

namespace Modules\GuardianPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Attendance\Services\AttendanceService;
use Modules\GuardianPortal\Services\GuardianPortalService;

/**
 * Guardian/student attendance history for one child. As with every
 * specific-student lookup in this portal, access is gated through
 * GuardianPortalService::assertOwnsChild() before any data is read, so
 * neither a guardian nor a student can view another family's records by
 * guessing a student id in the URL.
 */
class PortalAttendanceController extends Controller
{
    public function __construct(
        private readonly GuardianPortalService $portal,
        private readonly AttendanceService $attendance,
    ) {}

    public function index(Request $request)
    {
        $student = $this->portal->assertOwnsChild($request->user(), (int) $request->route('student'));
        $history = $this->attendance->historyForStudent($student->id);

        return view('guardianportal::attendance', compact('student', 'history'));
    }
}
