<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Payroll\Models\StaffSalary;
use Modules\Payroll\Services\PayrollService;

class GenerateController extends Controller
{
    public function create()
    {
        $assignedCount = StaffSalary::count();
        $period = now()->format('Y-m');

        return view('payroll::generate.create', compact('assignedCount', 'period'));
    }

    public function store(Request $request, PayrollService $payroll)
    {
        $request->validate([
            'period' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $n = $payroll->generateBatch($request->period);

        return redirect()->route('payroll.payslips.index', ['period' => $request->period])
            ->with('status', $n . ' payslip(s) generated for ' . $request->period . '.');
    }
}
