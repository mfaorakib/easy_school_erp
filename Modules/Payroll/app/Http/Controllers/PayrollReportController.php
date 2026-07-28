<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Services\PayrollService;

class PayrollReportController extends Controller
{
    public function summary(Request $request, PayrollService $payroll)
    {
        $period = $request->input('period', now()->format('Y-m'));

        $data = $payroll->summary($period);

        $payslips = Payslip::period($period)->with('staff')->orderBy('id')->get();

        $periods = Payslip::select('period')->distinct()->orderByDesc('period')->pluck('period');

        return view('payroll::summary', compact('data', 'period', 'payslips', 'periods'));
    }
}
