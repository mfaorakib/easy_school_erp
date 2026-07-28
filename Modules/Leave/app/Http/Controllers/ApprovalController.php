<?php

namespace Modules\Leave\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Leave\Models\LeaveApplication;
use Modules\Leave\Services\LeaveService;

class ApprovalController extends Controller
{
    public function index()
    {
        $pending = LeaveApplication::status('pending')
            ->with(['staff', 'type'])
            ->latest('id')
            ->get();

        return view('leave::approvals.index', compact('pending'));
    }

    public function review(Request $request, LeaveApplication $application, LeaveService $leave)
    {
        $request->validate([
            'status'      => ['required', 'in:approved,rejected'],
            'review_note' => ['nullable', 'string', 'max:255'],
        ]);

        $leave->review($application, $request->status, $request->review_note);

        return redirect()->route('leave.approvals.index')
            ->with('status', 'Application ' . $request->status . '.');
    }

    public function balance(Request $request, LeaveService $leave)
    {
        $staff = \Modules\HumanResource\Models\Staff::where('is_active', true)
            ->orderBy('full_name')
            ->get();

        $staffId = $request->integer('staff_id') ?: null;
        $sheet = $staffId ? $leave->balanceSheet($staffId) : null;

        return view('leave::balance', compact('staff', 'staffId', 'sheet'));
    }
}
