<?php

namespace Modules\Fees\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\AcademicCore\Models\Student;
use Modules\Accounting\Models\AccountTransaction;
use Modules\Accounting\Services\AccountingService;
use Modules\Fees\Models\StudentFine;
use Modules\Foundation\Services\SettingService;
use Modules\Foundation\Support\IdPattern;

/**
 * Ad-hoc, one-off student fines (disciplinary/other reasons) — deliberately
 * separate from FeeService's structured Fee Group/Type/Master/Assignment
 * hierarchy. Own receipt sequence/setting key (`fees.fine_receipt_format`),
 * never shares Fees' own `fees.receipt_format` sequence.
 */
class StudentFineService
{
    private const DEFAULT_RECEIPT_FORMAT = 'FINE-{YYYY}-{SEQ:5}';

    public function __construct(
        private readonly SettingService $settings,
        private readonly AccountingService $accounting,
    ) {}

    /** The next formatted receipt number for a student fine (own pattern/sequence, not Fees' own). */
    public function generateReceiptNo(): string
    {
        $pattern = $this->settings->get('fees.fine_receipt_format', self::DEFAULT_RECEIPT_FORMAT);
        $seq = StudentFine::withTrashed()->allYears()->whereNotNull('receipt_no')->count() + 1;

        return IdPattern::format($pattern, (int) date('Y'), $seq);
    }

    /** Record a new fine against a student (not yet collected). */
    public function impose(Student $student, string $reason, float $amount, ?string $date = null): StudentFine
    {
        return StudentFine::create([
            'student_id' => $student->id,
            'receipt_no' => $this->generateReceiptNo(),
            'reason'     => $reason,
            'amount'     => $amount,
            'fine_date'  => $date ?? now()->toDateString(),
            'imposed_by' => Auth::id(),
        ]);
    }

    /**
     * Collect a previously imposed fine and post it into Accounting as real
     * income. Idempotent via AccountingService::postFromSource(), but also
     * guarded here directly so a re-collect attempt gets a clear error
     * message rather than a silent no-op.
     */
    public function collect(StudentFine $fine): ?AccountTransaction
    {
        if ($fine->is_collected) {
            throw new \RuntimeException('This fine has already been collected.');
        }

        return DB::transaction(function () use ($fine) {
            $this->accounting->postFromSource(
                'student_fine',
                $fine->id,
                'income',
                'Student Fines',
                (float) $fine->amount,
                $fine->fine_date->toDateString(),
                'Student fine — '.$fine->reason.' ('.$fine->student->full_name.')',
            );

            $fine->update(['is_collected' => true, 'collected_at' => now()]);

            return $this->accounting->transactionFor('student_fine', $fine->id);
        });
    }

    /** Fines list filtered by student class/section and collection status. */
    public function filterStudentFines(?int $classId, ?int $sectionId, ?string $status): \Illuminate\Support\Collection
    {
        $query = StudentFine::with([
            'student.records' => fn ($q) => $q->live()->with(['schoolClass', 'section']),
            'imposedBy',
        ])->latest('fine_date');

        if ($classId) {
            $query->whereHas('student.records', fn ($q) => $q->live()
                ->where('class_id', $classId)
                ->when($sectionId, fn ($q2) => $q2->where('section_id', $sectionId)));
        }

        if ($status === 'collected') {
            $query->where('is_collected', true);
        } elseif ($status === 'pending') {
            $query->where('is_collected', false);
        }

        return $query->get();
    }

    /** Active students for the picker/filter, optionally narrowed to a class/section. */
    public function studentsForFilter(?int $classId, ?int $sectionId): \Illuminate\Support\Collection
    {
        return Student::where('is_active', true)
            ->with(['records' => fn ($q) => $q->live()->with(['schoolClass', 'section'])])
            ->when($classId, fn ($q) => $q->whereHas('records', fn ($q2) => $q2->live()
                ->where('class_id', $classId)
                ->when($sectionId, fn ($q3) => $q3->where('section_id', $sectionId))))
            ->orderBy('full_name')
            ->get();
    }
}
