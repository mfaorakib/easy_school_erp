<?php

namespace Modules\Fees\Services;

use App\Core\Support\Context;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\AcademicCore\Models\StudentRecord;
use Modules\Accounting\Services\AccountingService;
use Modules\Fees\Models\FeeAssignment;
use Modules\Fees\Models\FeeCarryForward;
use Modules\Fees\Models\FeeDiscount;
use Modules\Fees\Models\FeeMaster;
use Modules\Fees\Models\FeePayment;
use Modules\Foundation\Services\SettingService;
use Modules\Foundation\Support\IdPattern;

/**
 * Fee assignment, balance computation, discount adjustment and collection.
 *
 * Money model (per assignment):
 *   net      = master.amount − assignment-level discounts
 *   settled  = Σ(payment.amount + payment.discount)   // discount settles principal
 *   due      = max(0, net − settled)
 *   fine_due = master fine if due > 0 and past due date (informational; collected per payment)
 *   status   = settled ≤ 0 → unpaid | due ≤ 0 → paid | else partial
 */
class FeeService
{
    private const DEFAULT_RECEIPT_FORMAT = 'RCT-{YYYY}-{SEQ:5}';

    public function __construct(
        private readonly SettingService $settings,
        private readonly AccountingService $accounting,
    ) {}

    /** Assign a fee master to every live student of its class (or a section). */
    public function assignToClass(FeeMaster $master, ?int $sectionId = null): int
    {
        $records = StudentRecord::live()
            ->when($master->class_id, fn ($q) => $q->where('class_id', $master->class_id))
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->get();

        $count = 0;
        foreach ($records as $record) {
            $created = FeeAssignment::firstOrCreate(
                ['fee_master_id' => $master->id, 'student_id' => $record->student_id],
                ['academic_year_id' => $master->academic_year_id, 'status' => 'unpaid'],
            )->wasRecentlyCreated;

            $count += $created ? 1 : 0;
        }

        return $count;
    }

    public function assignToStudent(FeeMaster $master, int $studentId): FeeAssignment
    {
        return FeeAssignment::firstOrCreate(
            ['fee_master_id' => $master->id, 'student_id' => $studentId],
            ['academic_year_id' => $master->academic_year_id, 'status' => 'unpaid'],
        );
    }

    /** @return array{amount:float,discount:float,net:float,settled:float,paid:float,due:float,fine_due:float,status:string} */
    public function balance(FeeAssignment $assignment, ?\DateTimeInterface $on = null): array
    {
        $on ??= now();
        $assignment->loadMissing(['master', 'discounts', 'payments']);
        $master = $assignment->master;

        $amount   = (float) $master->amount;
        $discount = $assignment->discounts->sum(fn ($d) => $d->resolve($amount));
        $net      = max(0, round($amount - $discount, 2));

        $paid          = (float) $assignment->payments->sum('amount');
        $payDiscount   = (float) $assignment->payments->sum('discount');
        $fineCollected = (float) $assignment->payments->sum('fine');
        $refunded      = (float) $assignment->payments->sum('refunded_amount');

        // A fine is an EXTRA charge that was never part of `settled` (only
        // `amount` + `discount` pay down tuition). A refund is therefore
        // applied to the collected-fine bucket FIRST, and only the portion of
        // the refund that exceeds all collected fines reduces the tuition
        // actually settled. Without this, refunding a fine wrongly re-bills
        // already-paid tuition, and a large refund could push `due` above
        // `net`. `principalRefunded` is clamped ≥ 0 so it can't underflow.
        $principalRefunded = max(0, round($refunded - $fineCollected, 2));
        $settled           = round($paid + $payDiscount - $principalRefunded, 2);
        $due               = max(0, round($net - $settled, 2));

        $fineDue = $due > 0 ? $master->fineFor($on) : 0.0;

        // Check "fully settled" BEFORE "nothing paid" — a 100% waiver (net 0)
        // owes nothing and must read as paid, not stuck on unpaid.
        $status = $due <= 0 ? 'paid' : ($settled <= 0 ? 'unpaid' : 'partial');

        return compact('amount', 'discount', 'net', 'settled', 'paid', 'due', 'fineDue', 'status');
    }

    /**
     * Attach a discount/scholarship/waiver to one student's fee assignment,
     * with a mandatory reason and the approving user — this is the piece that
     * was previously missing entirely (FeeDiscount existed but nothing ever
     * applied one to a student). Re-attaching the same discount just updates
     * the reason/approver rather than duplicating the pivot row.
     */
    public function attachDiscount(FeeAssignment $assignment, FeeDiscount $discount, string $reason, User $approver): void
    {
        $assignment->discounts()->syncWithoutDetaching([
            $discount->id => ['reason' => $reason, 'applied_by' => $approver->id, 'applied_at' => now()],
        ]);

        $assignment->update(['status' => $this->balance($assignment->fresh())['status']]);
    }

    public function detachDiscount(FeeAssignment $assignment, FeeDiscount $discount): void
    {
        $assignment->discounts()->detach($discount->id);
        $assignment->update(['status' => $this->balance($assignment->fresh())['status']]);
    }

    /**
     * Collect a payment against an assignment and refresh its status. Used by
     * both the admin manual-collection screen and, via FeePaymentIntentService,
     * a confirmed online gateway payment — one code path, one receipt scheme,
     * regardless of who or what triggered it.
     */
    public function collect(FeeAssignment $assignment, array $data): FeePayment
    {
        return DB::transaction(function () use ($assignment, $data) {
            $payment = $assignment->payments()->create([
                'receipt_no'  => $this->generateReceiptNo(),
                'amount'      => $data['amount'] ?? 0,
                'fine'        => $data['fine'] ?? 0,
                'discount'    => $data['discount'] ?? 0,
                'paid_on'     => $data['paid_on'] ?? now()->toDateString(),
                'method'      => $data['method'] ?? 'cash',
                'reference'   => $data['reference'] ?? null,
                'note'        => $data['note'] ?? null,
                'received_by' => $data['received_by'] ?? Auth::id(),
            ]);

            $this->accounting->postFromSource(
                'fees',
                $payment->id,
                'income',
                'Tuition Fees',
                (float) $payment->amount + (float) $payment->fine,
                $payment->paid_on,
                'Fee payment — receipt '.$payment->receipt_no,
            );

            $assignment->update(['status' => $this->balance($assignment->fresh())['status']]);

            return $payment;
        });
    }

    /**
     * Refund part or all of an already-collected payment — never edits or
     * deletes the payment itself (its receipt must always show what was
     * really collected), just accumulates refunded_amount and posts a linked
     * accounting adjustment (an income reduction) against the original
     * collection entry, then refreshes the assignment's status/due.
     */
    public function refund(FeePayment $payment, float $amount, string $reason): void
    {
        $refundable = $payment->refundableAmount();
        if ($amount <= 0 || $amount > $refundable) {
            throw new \RuntimeException("Refund amount must be between 0.01 and {$refundable}.");
        }

        DB::transaction(function () use ($payment, $amount, $reason) {
            $payment->increment('refunded_amount', $amount);

            $original = $this->accounting->transactionFor('fees', $payment->id);
            if ($original) {
                $this->accounting->postAdjustment($original, $amount, $reason);
            }

            $assignment = $payment->assignment;
            $assignment->update(['status' => $this->balance($assignment->fresh())['status']]);
        });
    }

    /** The next formatted receipt number, per the admin-configurable pattern (same IdPattern used for admission unique IDs). */
    public function generateReceiptNo(): string
    {
        $pattern = $this->settings->get('fees.receipt_format', self::DEFAULT_RECEIPT_FORMAT);
        // Count trashed rows too — a soft-deleted payment still holds its
        // receipt_no under the UNIQUE index, so excluding them would recompute
        // an already-taken number and 500 on the next collect (mirrors
        // StudentFineService's withTrashed() approach).
        $seq     = FeePayment::withTrashed()->whereNotNull('receipt_no')->count() + 1;

        return IdPattern::format($pattern, (int) date('Y'), $seq);
    }

    /**
     * Carry each student's unpaid balance from one year into another as an
     * opening due (idempotent per student+year).
     */
    public function carryForwardUnpaid(int $fromYearId, int $toYearId): int
    {
        $assignments = FeeAssignment::allYears()
            ->where('academic_year_id', $fromYearId)
            ->where('status', '!=', 'paid')
            ->with(['master', 'discounts', 'payments'])
            ->get()
            ->groupBy('student_id');

        $count = 0;
        foreach ($assignments as $studentId => $group) {
            $due = round($group->sum(fn ($a) => $this->balance($a)['due']), 2);
            if ($due <= 0) {
                continue;
            }

            FeeCarryForward::updateOrCreate(
                ['student_id' => $studentId, 'academic_year_id' => $toYearId, 'from_year_id' => $fromYearId],
                ['amount' => $due, 'paid' => 0, 'note' => 'Carried forward unpaid balance'],
            );
            $count++;
        }

        return $count;
    }

    /** All fee assignments for a student in the active year, with balances. */
    public function studentLedger(int $studentId): array
    {
        return FeeAssignment::where('student_id', $studentId)
            ->with(['master.group', 'master.type', 'discounts', 'payments'])
            ->get()
            ->map(fn ($a) => ['assignment' => $a, 'balance' => $this->balance($a)])
            ->all();
    }
}
