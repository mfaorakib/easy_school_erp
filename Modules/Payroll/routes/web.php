<?php

use Illuminate\Support\Facades\Route;
use Modules\Payroll\Http\Controllers\GenerateController;
use Modules\Payroll\Http\Controllers\PayrollReportController;
use Modules\Payroll\Http\Controllers\PayslipController;
use Modules\Payroll\Http\Controllers\SalaryAdvanceController;
use Modules\Payroll\Http\Controllers\SalaryTemplateController;
use Modules\Payroll\Http\Controllers\StaffSalaryController;

Route::middleware('auth')->prefix('payroll')->name('payroll.')->group(function () {
    // Salary templates + components (Agent A)
    Route::resource('templates', SalaryTemplateController::class)->except('show')->parameters(['templates' => 'template']);
    Route::post('templates/{template}/components', [SalaryTemplateController::class, 'addComponent'])->name('templates.components.add');
    Route::delete('components/{component}', [SalaryTemplateController::class, 'removeComponent'])->name('templates.components.remove');

    // Staff salary assignment + payroll generation (Agent B)
    Route::get('staff-salaries', [StaffSalaryController::class, 'index'])->name('staff.index');
    Route::post('staff-salaries', [StaffSalaryController::class, 'assign'])->name('staff.assign');
    Route::get('generate', [GenerateController::class, 'create'])->name('generate.create');
    Route::post('generate', [GenerateController::class, 'store'])->name('generate.store');

    // Payslips (Agent C)
    Route::get('payslips', [PayslipController::class, 'index'])->name('payslips.index');

    // Bulk bank/cash payment run + printable bank advice list for a period —
    // these literal paths MUST be registered before payslips/{payslip} below,
    // or "bank-advice"/"bulk-pay" gets swallowed as a {payslip} id and 404s.
    Route::post('payslips/bulk-pay', [PayslipController::class, 'bulkPay'])->name('payslips.bulkPay');
    Route::get('payslips/bank-advice', [PayslipController::class, 'bankAdvice'])->name('payslips.bankAdvice');

    Route::get('payslips/{payslip}', [PayslipController::class, 'show'])->name('payslips.show');
    Route::post('payslips/{payslip}/pay', [PayslipController::class, 'markPaid'])->name('payslips.pay');
    Route::delete('payslips/{payslip}', [PayslipController::class, 'destroy'])->name('payslips.destroy');

    // Salary advances — admin review (staff request these from the Staff Portal)
    Route::get('advances', [SalaryAdvanceController::class, 'index'])->name('advances.index');
    Route::post('advances/{advance}/review', [SalaryAdvanceController::class, 'review'])->name('advances.review');

    // Summary report (Agent D)
    Route::get('summary', [PayrollReportController::class, 'summary'])->name('summary');
});
