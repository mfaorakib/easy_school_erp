<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HumanResource\Models\Staff;
use Modules\Payroll\Models\SalaryTemplate;
use Modules\Payroll\Models\StaffSalary;
use Modules\Payroll\Services\PayrollService;

class StaffSalaryController extends Controller
{
    public function index()
    {
        $staff = Staff::where('is_active', true)->orderBy('full_name')->get();
        $templates = SalaryTemplate::active()->orderBy('name')->get();
        $assigned = StaffSalary::with('template')->get()->keyBy('staff_id');

        return view('payroll::staff.index', compact('staff', 'templates', 'assigned'));
    }

    public function assign(Request $request, PayrollService $payroll)
    {
        $request->validate([
            'staff_id' => ['required', 'exists:staff,id'],
            'salary_template_id' => ['required', 'exists:salary_templates,id'],
        ]);

        $payroll->assignTemplate((int) $request->staff_id, (int) $request->salary_template_id);

        return redirect()->route('payroll.staff.index')->with('status', 'Template assigned.');
    }
}
