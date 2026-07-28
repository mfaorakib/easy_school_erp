<?php

namespace Modules\Reports\Services;

use Illuminate\Support\Collection;
use Modules\AcademicCore\Models\StudentRecord;
use Modules\Examination\Models\ExamResult;
use Modules\Fees\Models\FeeAssignment;
use Modules\Fees\Models\FeePayment;
use Modules\Fees\Services\FeeService;
use Modules\Wallet\Models\WalletTransaction;

/**
 * Read-only cross-module reporting. The Reports module owns NO tables — every
 * method here queries other modules' data and returns plain shaped rows the
 * controllers render. All reads are academic-year scoped via the source models'
 * global scopes; the live roster is always student_records where is_promote = 0.
 */
class ReportService
{
    public function __construct(private FeeService $fees) {}

    // ---------------------------------------------------------------- People

    /**
     * Live students, optionally filtered by class/section.
     * @return Collection<int,array{record:StudentRecord, student:\Modules\AcademicCore\Models\Student}>
     */
    public function studentList(?int $classId = null, ?int $sectionId = null): Collection
    {
        return StudentRecord::live()
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->with(['student.guardian', 'schoolClass', 'section'])
            ->orderBy('class_id')->orderBy('section_id')->orderBy('roll_no')
            ->get()
            ->filter(fn ($r) => $r->student !== null)
            ->map(fn ($r) => ['record' => $r, 'student' => $r->student])
            ->values();
    }

    /**
     * Guardian / contact directory for the live roster (one row per student,
     * showing the linked guardian's contacts).
     * @return Collection<int,array{record:StudentRecord, student:mixed, guardian:mixed}>
     */
    public function guardianList(?int $classId = null, ?int $sectionId = null): Collection
    {
        return $this->studentList($classId, $sectionId)
            ->map(fn ($row) => [
                'record'   => $row['record'],
                'student'  => $row['student'],
                'guardian' => $row['student']->guardian,
            ]);
    }

    // ------------------------------------------------------------ Attendance

    /**
     * Per-student attendance tallies for a class/section over a date range.
     * Counts each status code (P/L/A/F/H) and a present-rate %.
     * @return array{rows: array<int,array<string,mixed>>, from:string, to:string}
     */
    public function attendanceSummary(int $classId, int $sectionId, string $from, string $to): array
    {
        $records = StudentRecord::live()
            ->where('class_id', $classId)->where('section_id', $sectionId)
            ->with('student')->orderBy('roll_no')->get()
            ->filter(fn ($r) => $r->student !== null);

        $studentIds = $records->pluck('student_id')->all();

        // status is an AttendanceStatus enum cast → compare on ->value.
        $tallies = \Modules\Attendance\Models\StudentAttendance::query()
            ->whereIn('student_id', $studentIds)
            ->whereBetween('date', [$from, $to])
            ->get()
            ->groupBy('student_id');

        $rows = $records->map(function ($r) use ($tallies) {
            $rows = $tallies->get($r->student_id) ?? collect();
            $count = fn (string $code) => $rows->filter(fn ($a) => $a->status?->value === $code)->count();

            $p = $count('P'); $l = $count('L'); $a = $count('A'); $f = $count('F'); $h = $count('H');
            $marked = $p + $l + $a + $f; // holidays excluded from the rate denominator
            $rate = $marked > 0 ? round(($p + $l + $f * 0.5) / $marked * 100, 1) : 0.0;

            return [
                'roll_no' => $r->roll_no,
                'student' => $r->student,
                'present' => $p, 'late' => $l, 'absent' => $a,
                'half' => $f, 'holiday' => $h, 'total' => $marked, 'rate' => $rate,
            ];
        })->values()->all();

        return ['rows' => $rows, 'from' => $from, 'to' => $to];
    }

    // ------------------------------------------------------------------ Fees

    /**
     * Fee payments collected in a date range (optionally a class), with totals.
     * @return array{payments:Collection, total_amount:float, total_fine:float, total_discount:float}
     */
    public function feesCollection(string $from, string $to, ?int $classId = null): array
    {
        $payments = FeePayment::query()
            ->whereBetween('paid_on', [$from, $to])
            ->with(['assignment.student', 'assignment.master.type', 'receiver'])
            ->when($classId, fn ($q) => $q->whereHas('assignment.master', fn ($m) => $m->where('class_id', $classId)))
            ->orderBy('paid_on')
            ->get();

        return [
            'payments'       => $payments,
            'total_amount'   => (float) $payments->sum('amount'),
            'total_fine'     => (float) $payments->sum('fine'),
            'total_discount' => (float) $payments->sum('discount'),
        ];
    }

    /**
     * Outstanding dues for live students (optionally class/section filtered).
     * Aggregates every assignment's due via FeeService->balance.
     * @return array{rows: array<int,array{student:mixed, net:float, settled:float, due:float}>, total_due:float}
     */
    public function feesDue(?int $classId = null, ?int $sectionId = null): array
    {
        $records = StudentRecord::live()
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->with('student')->orderBy('roll_no')->get()
            ->filter(fn ($r) => $r->student !== null);

        $rows = [];
        $totalDue = 0.0;
        foreach ($records as $r) {
            $assignments = FeeAssignment::where('student_id', $r->student_id)
                ->with(['master', 'discounts', 'payments'])->get();

            $net = 0.0; $settled = 0.0; $due = 0.0;
            foreach ($assignments as $a) {
                $b = $this->fees->balance($a);
                $net += $b['net']; $settled += $b['settled']; $due += $b['due'];
            }

            if ($due <= 0) {
                continue; // only students who actually owe
            }

            $totalDue += $due;
            $rows[] = [
                'roll_no' => $r->roll_no,
                'student' => $r->student,
                'net'     => round($net, 2),
                'settled' => round($settled, 2),
                'due'     => round($due, 2),
            ];
        }

        return ['rows' => $rows, 'total_due' => round($totalDue, 2)];
    }

    // ---------------------------------------------------------------- Wallet

    /**
     * Wallet movements in a date range with credit/debit totals.
     * @return array{transactions:Collection, total_credit:float, total_debit:float}
     */
    public function walletReport(string $from, string $to): array
    {
        $tx = WalletTransaction::query()
            ->whereBetween('transacted_on', [$from, $to])
            ->with('wallet.user')
            ->orderBy('transacted_on')
            ->get();

        return [
            'transactions' => $tx,
            'total_credit' => (float) $tx->where('type', 'credit')->sum('amount'),
            'total_debit'  => (float) $tx->where('type', 'debit')->sum('amount'),
        ];
    }

    // -------------------------------------------------------------- Examination

    /**
     * Computed results for an exam/class/section, ranked by stored position.
     * @return Collection<int,ExamResult>
     */
    public function examResults(int $examId, int $classId, int $sectionId): Collection
    {
        return ExamResult::where('exam_id', $examId)
            ->where('class_id', $classId)->where('section_id', $sectionId)
            ->with('student')
            ->orderBy('position')
            ->get();
    }

    /** Top-N passing students of an exam/class/section (merit order). */
    public function meritList(int $examId, int $classId, int $sectionId, int $limit = 10): Collection
    {
        return $this->examResults($examId, $classId, $sectionId)
            ->where('is_pass', true)
            ->take($limit)
            ->values();
    }
}
