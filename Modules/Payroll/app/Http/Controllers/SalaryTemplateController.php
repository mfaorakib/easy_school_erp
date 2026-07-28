<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Payroll\Models\SalaryComponent;
use Modules\Payroll\Models\SalaryTemplate;
use Modules\Payroll\Services\PayrollService;

class SalaryTemplateController extends Controller
{
    public function index(PayrollService $payroll)
    {
        $templates = SalaryTemplate::withCount('components')->orderBy('name')->get();
        $totals = $templates->mapWithKeys(fn ($t) => [$t->id => $payroll->templateTotals($t)]);

        return view('payroll::templates.index', compact('templates', 'totals'));
    }

    public function create()
    {
        return view('payroll::templates.form', ['template' => new SalaryTemplate, 'totals' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:150'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $t = SalaryTemplate::create($data);

        return redirect()->route('payroll.templates.edit', $t)->with('status', 'Template created — add components.');
    }

    public function edit(SalaryTemplate $template, PayrollService $payroll)
    {
        $template->load('components');
        $totals = $payroll->templateTotals($template);

        return view('payroll::templates.form', compact('template', 'totals'));
    }

    public function update(Request $request, SalaryTemplate $template)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:150'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $template->update($data);

        return redirect()->route('payroll.templates.index')->with('status', 'Template updated.');
    }

    public function destroy(SalaryTemplate $template)
    {
        $template->delete();

        return redirect()->route('payroll.templates.index')->with('status', 'Template deleted.');
    }

    public function addComponent(Request $request, SalaryTemplate $template)
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'type'      => ['required', 'in:earning,deduction'],
            'calc_type' => ['required', 'in:fixed,percent'],
            'value'     => ['required', 'numeric', 'min:0'],
        ]);

        $position = (int) $template->components()->max('position') + 1;
        $template->components()->create($request->only('name', 'type', 'calc_type', 'value') + ['position' => $position]);

        return redirect()->route('payroll.templates.edit', $template)->with('status', 'Component added.');
    }

    public function removeComponent(SalaryComponent $component)
    {
        $t = $component->template;
        $component->delete();

        return redirect()->route('payroll.templates.edit', $t)->with('status', 'Component removed.');
    }
}
