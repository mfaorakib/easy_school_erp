<?php

namespace Modules\Payroll\Services;

use App\Core\Support\Context;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Services\AccountingService;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Models\SalaryTemplate;
use Modules\Payroll\Models\StaffSalary;

/**
 * Payroll engine.
 *
 * A salary template resolves to: basic + Σ earning components − Σ deduction
 * components (a component is a fixed amount or a percent of basic). Generating
 * payroll for a month snapshots that resolution into a payslip + its line items,
 * so later edits to the template never rewrite history. One payslip per
 * (staff, period); an existing payslip is never overwritten (paid ones stay safe).
 */
class PayrollService
{
    public function __construct(
        private readonly AccountingService $accounting,
        private readonly SalaryAdvanceService $advances,
    ) {}

    /** Resolve a template's money totals. @return array{basic:float, earnings:float, deductions:float, gross:float, net:float, items:array} */
    public function templateTotals(SalaryTemplate $template): array
    {
        $basic = (float) $template->basic_salary;
        $earnings = 0.0;
        $deductions = 0.0;
        $items = [];

        foreach ($template->components as $component) {
            $amount = $component->resolve($basic);
            $items[] = ['name' => $component->name, 'type' => $component->type, 'amount' => $amount];
            if ($component->type === 'earning') {
                $earnings += $amount;
            } else {
                $deductions += $amount;
            }
        }

        $gross = round($basic + $earnings, 2);

        return [
            'basic'      => round($basic, 2),
            'earnings'   => round($earnings, 2),
            'deductions' => round($deductions, 2),
            'gross'      => $gross,
            'net'        => round($gross - $deductions, 2),
            'items'      => $items,
        ];
    }

    /** Assign (or re-assign) a template to a staff member. */
    public function assignTemplate(int $staffId, int $templateId): StaffSalary
    {
        return StaffSalary::updateOrCreate(
            ['staff_id' => $staffId],
            ['salary_template_id' => $templateId, 'academic_year_id' => Context::academicYearId()],
        );
    }

    /**
     * Generate a payslip for one staff member for a period (YYYY-MM) from their
     * assigned template. Returns null if they have no template or a payslip for
     * that period already exists.
     */
    public function generateFor(int $staffId, string $period): ?Payslip
    {
        $assignment = StaffSalary::where('staff_id', $staffId)->with('template.components')->first();
        if (! $assignment || ! $assignment->template) {
            return null;
        }
        // withTrashed(): a soft-deleted payslip still occupies the (staff_id,
        // period) UNIQUE index slot, so a non-trashed exists() would say "not
        // there", attempt create(), and 500 on the unique index. Treat a
        // trashed payslip as already-existing (no-op) instead of crashing.
        if (Payslip::withTrashed()->where('staff_id', $staffId)->where('period', $period)->exists()) {
            return null;
        }

        $totals = $this->templateTotals($assignment->template);
        $advanceDeduction = $this->advances->calculateDeduction($staffId, $totals['net']);

        return DB::transaction(function () use ($staffId, $period, $assignment, $totals, $advanceDeduction) {
            $payslip = Payslip::create([
                'staff_id'           => $staffId,
                'salary_template_id' => $assignment->salary_template_id,
                'period'             => $period,
                'basic_salary'       => $totals['basic'],
                'gross_earnings'     => $totals['gross'],
                'total_deductions'   => $totals['deductions'],
                'net_pay'            => $totals['net'],
                'advance_deduction'  => $advanceDeduction,
                'status'             => Payslip::STATUS_GENERATED,
                'generated_by'       => Auth::id(),
            ]);

            // The basic pay is itself the first earning line.
            $payslip->items()->create(['name' => 'Basic Salary', 'type' => 'earning', 'amount' => $totals['basic']]);
            foreach ($totals['items'] as $item) {
                $payslip->items()->create($item);
            }

            return $payslip;
        });
    }

    /**
     * Generate payslips for every staff member with an assigned template for a
     * period. Returns the number of payslips created (skips already-generated).
     */
    public function generateBatch(string $period): int
    {
        $staffIds = StaffSalary::pluck('staff_id');
        $count = 0;
        foreach ($staffIds as $staffId) {
            if ($this->generateFor($staffId, $period)) {
                $count++;
            }
        }

        return $count;
    }

    public function markPaid(Payslip $payslip, string $method = 'cash', ?string $date = null, ?int $bankAccountId = null): Payslip
    {
        if ($payslip->isPaid()) {
            return $payslip;
        }

        // Whole thing is atomic: the advance recovery, the paid status, and the
        // accounting expense must all commit together or not at all — otherwise
        // a mid-way failure could leave a recovery committed with no matching
        // expense (or vice-versa), and the paid-guard would block any retry.
        return DB::transaction(function () use ($payslip, $method, $date, $bankAccountId) {
            // Reconcile the advance deduction to the outstanding balance AS OF
            // NOW — not the amount snapshotted at generation. Between generation
            // and payment, other payslips may already have recovered part of
            // the advance; the stale snapshot would over-withhold (docking the
            // staff more than is actually recovered, the difference vanishing
            // from both their pay and the posted expense). commitDeduction()
            // caps each allocation at the real outstanding, so advance_deduction
            // must equal what will actually be recovered for payableAmount() —
            // net_pay − advance_deduction — to be correct.
            $actualDeduction = $this->advances->calculateDeduction($payslip->staff_id, (float) $payslip->net_pay);

            $payslip->update([
                'status'            => Payslip::STATUS_PAID,
                'payment_method'    => $method,
                'bank_account_id'   => $method === 'bank' ? $bankAccountId : null,
                'paid_on'           => $date ?? now()->toDateString(),
                'advance_deduction' => $actualDeduction,
            ]);

            // Commit the advance recovery FIRST — postFromSource below must post
            // only what's actually being paid out this time (net pay minus
            // whatever is being recovered), never the full net pay, or the
            // advanced portion would be counted as an expense twice.
            $this->advances->commitDeduction($payslip);

            $this->accounting->postFromSource(
                'payroll',
                $payslip->id,
                'expense',
                'Salaries',
                $payslip->payableAmount(),
                $payslip->paid_on,
                'Salary payment — payslip #'.$payslip->id.' ('.$payslip->period.')',
                $method === 'bank' ? $bankAccountId : null,
            );

            return $payslip;
        });
    }

    /**
     * Mark several generated payslips paid in one go — same period/method/
     * bank account for all of them (a school running one bank-transfer or
     * cash-disbursement batch for a payroll period). Skips anything already
     * paid or not in the given id list; returns how many were actually paid.
     */
    public function bulkMarkPaid(array $payslipIds, string $method, ?int $bankAccountId = null, ?string $date = null): int
    {
        $payslips = Payslip::whereIn('id', $payslipIds)
            ->where('status', Payslip::STATUS_GENERATED)
            ->get();

        foreach ($payslips as $payslip) {
            $this->markPaid($payslip, $method, $date, $bankAccountId);
        }

        return $payslips->count();
    }

    /** Bank-paid payslips for a period, with the staff's bank details — the printable "hand this to the bank" list. */
    public function bankAdviceList(string $period): Collection
    {
        return Payslip::period($period)
            ->where('payment_method', 'bank')
            ->with(['staff', 'bankAccount'])
            ->orderBy('id')
            ->get();
    }

    /** @return array{count:int, gross:float, deductions:float, net:float, paid:float, unpaid:float} */
    public function summary(string $period): array
    {
        $slips = Payslip::period($period)->get();

        return [
            'count'      => $slips->count(),
            'gross'      => round((float) $slips->sum('gross_earnings'), 2),
            'deductions' => round((float) $slips->sum('total_deductions'), 2),
            'net'        => round((float) $slips->sum('net_pay'), 2),
            'paid'       => round((float) $slips->where('status', 'paid')->sum('net_pay'), 2),
            'unpaid'     => round((float) $slips->where('status', 'generated')->sum('net_pay'), 2),
        ];
    }
}
