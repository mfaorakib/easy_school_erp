<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Payroll\Models\SalaryAdvance;
use Modules\Payroll\Services\SalaryAdvanceService;

class SalaryAdvanceController extends Controller
{
    public function index()
    {
        $pending = SalaryAdvance::pending()
            ->with('staff')
            ->latest('id')
            ->get();

        $history = SalaryAdvance::where('status', '!=', SalaryAdvance::STATUS_PENDING)
            ->with(['staff', 'reviewer'])
            ->latest('reviewed_at')
            ->limit(20)
            ->get();

        return view('payroll::advances.index', compact('pending', 'history'));
    }

    public function review(Request $request, SalaryAdvance $advance, SalaryAdvanceService $service)
    {
        $request->validate([
            'status'      => ['required', 'in:approved,rejected'],
            'review_note' => ['nullable', 'string', 'max:255'],
        ]);

        $service->review($advance, $request->status, $request->review_note);

        return redirect()->route('payroll.advances.index')
            ->with('status', 'Advance '.$request->status.'.');
    }
}
