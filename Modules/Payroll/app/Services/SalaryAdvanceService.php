<?php

namespace Modules\Payroll\Services;

use Illuminate\Support\Facades\Auth;
use Modules\Accounting\Services\AccountingService;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Models\SalaryAdvance;

/**
 * A salary advance is money paid to a staff member early, recovered from a
 * later payslip. Approving one is treated as disbursing it immediately (real
 * cash leaving the school right then) — it posts to Accounting as an expense
 * at that moment. Recovery later reduces what actually gets posted when the
 * recovering payslip is paid (see PayrollService::markPaid()), so the two
 * postings together equal the true total ever paid out — never double-counted.
 */
class SalaryAdvanceService
{
    public function __construct(private readonly AccountingService $accounting) {}

    public function request(int $staffId, float $amount, ?string $reason = null): SalaryAdvance
    {
        return SalaryAdvance::create([
            'staff_id'     => $staffId,
            'amount'       => $amount,
            'reason'       => $reason,
            'status'       => SalaryAdvance::STATUS_PENDING,
            'requested_at' => now()->toDateString(),
        ]);
    }

    public function review(SalaryAdvance $advance, string $status, ?string $note = null): SalaryAdvance
    {
        $advance->update([
            'status'      => $status,
            'review_note' => $note,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        if ($status === SalaryAdvance::STATUS_APPROVED) {
            $this->accounting->postFromSource(
                'salary_advance',
                $advance->id,
                'expense',
                'Salary Advance',
                (float) $advance->amount,
                now()->toDateString(),
                'Salary advance — '.$advance->staff->full_name,
            );
        }

        return $advance;
    }

    /** Total still owed back to the school across all of this staff member's approved advances. */
    public function outstandingFor(int $staffId): float
    {
        return round(
            (float) SalaryAdvance::outstanding()->where('staff_id', $staffId)
                ->get()->sum(fn ($a) => $a->outstandingAmount()),
            2,
        );
    }

    /** How much of a new payslip's net pay should be withheld to recover outstanding advances. Computed only — does not touch any advance record yet. */
    public function calculateDeduction(int $staffId, float $netPay): float
    {
        return round(min($this->outstandingFor($staffId), $netPay), 2);
    }

    /**
     * Actually allocate a payslip's already-computed advance_deduction against
     * the staff member's outstanding advances, oldest first — call this ONLY
     * when the payslip is actually being paid, never at generation time.
     */
    public function commitDeduction(Payslip $payslip): void
    {
        $remaining = (float) $payslip->advance_deduction;
        if ($remaining <= 0) {
            return;
        }

        $advances = SalaryAdvance::outstanding()
            ->where('staff_id', $payslip->staff_id)
            ->orderBy('requested_at')
            ->get();

        foreach ($advances as $advance) {
            if ($remaining <= 0) {
                break;
            }

            $apply = min($advance->outstandingAmount(), $remaining);
            $advance->increment('recovered_amount', $apply);
            $remaining -= $apply;
        }
    }
}
