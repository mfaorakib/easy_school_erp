<?php

namespace Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\HumanResource\Models\Staff;
use Modules\Payroll\Models\SalaryTemplate;
use Modules\Payroll\Services\PayrollService;

class PayrollDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $template = SalaryTemplate::firstOrCreate(
            ['name' => 'Teacher Grade'],
            ['basic_salary' => 30000],
        );

        if ($template->components()->count() === 0) {
            $components = [
                ['name' => 'House Rent', 'type' => 'earning', 'calc_type' => 'percent', 'value' => 40],
                ['name' => 'Medical Allowance', 'type' => 'earning', 'calc_type' => 'fixed', 'value' => 2000],
                ['name' => 'Provident Fund', 'type' => 'deduction', 'calc_type' => 'percent', 'value' => 10],
                ['name' => 'Income Tax', 'type' => 'deduction', 'calc_type' => 'fixed', 'value' => 1500],
            ];
            foreach ($components as $i => $c) {
                $template->components()->create($c + ['position' => $i + 1]);
            }
        }

        // Assign to the demo staff and generate the current month's payslip.
        $svc = app(PayrollService::class);
        $staff = Staff::where('is_active', true)->orderBy('id')->first();
        if ($staff) {
            $svc->assignTemplate($staff->id, $template->id);
            $svc->generateFor($staff->id, now()->format('Y-m'));
        }
    }
}
