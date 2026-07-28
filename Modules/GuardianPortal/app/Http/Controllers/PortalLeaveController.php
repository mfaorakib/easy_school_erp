<?php

namespace Modules\GuardianPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\GuardianPortal\Services\GuardianPortalService;
use Modules\Leave\Models\LeaveType;
use Modules\Leave\Services\StudentLeaveService;

/** Guardian/student portal — view and submit student leave requests for an owned child. */
class PortalLeaveController extends Controller
{
    public function __construct(
        protected GuardianPortalService $portal,
        protected StudentLeaveService $studentLeave,
    ) {
    }

    public function index(Request $request): View
    {
        $student = $this->portal->assertOwnsChild($request->user(), (int) $request->route('student'));

        $requests = $this->studentLeave->forStudent($student->id);
        $types = LeaveType::active()->orderBy('name')->get();

        return view('guardianportal::leave', compact('student', 'requests', 'types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $student = $this->portal->assertOwnsChild($request->user(), (int) $request->route('student'));

        $data = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'from_date'     => ['required', 'date'],
            'to_date'       => ['required', 'date', 'after_or_equal:from_date'],
            'reason'        => ['nullable', 'string', 'max:1000'],
        ]);

        $data['student_id'] = $student->id;
        $this->studentLeave->apply($data);

        return redirect()
            ->route('portal.children.leave.index', $student->id)
            ->with('status', __('ui.leave_request_submitted'));
    }
}
