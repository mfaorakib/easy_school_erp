<?php

namespace Modules\Leave\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Leave\Models\StudentLeaveApplication;
use Modules\Leave\Services\StudentLeaveService;

class StudentLeaveController extends Controller
{
    public function index(StudentLeaveService $studentLeave)
    {
        $pending = $studentLeave->pending();

        return view('leave::student-leave.index', compact('pending'));
    }

    public function review(Request $request, StudentLeaveApplication $application, StudentLeaveService $studentLeave)
    {
        $request->validate([
            'status'      => ['required', 'in:approved,rejected'],
            'review_note' => ['nullable', 'string', 'max:255'],
        ]);

        $studentLeave->review($application, $request->status, auth()->id(), $request->review_note);

        return redirect()->route('leave.student.index')
            ->with('status', 'Application ' . $request->status . '.');
    }
}
