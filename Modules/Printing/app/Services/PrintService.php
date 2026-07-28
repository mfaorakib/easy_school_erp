<?php

namespace Modules\Printing\Services;

use Modules\AcademicCore\Models\Student;
use Modules\AcademicCore\Models\StudentRecord;
use Modules\Builder\Models\SiteSetting;
use Modules\Examination\Models\Exam;
use Modules\Examination\Models\ExamResult;
use Modules\Examination\Models\ExamSchedule;
use Modules\Examination\Models\ExamSeatPlan;
use Modules\Examination\Models\GradeScale;
use Modules\Examination\Models\Mark;
use Modules\Examination\Services\SeatPlanService;
use Modules\Fees\Services\FeeService;

/**
 * Builds the data behind the printable exam & fee documents (marksheet,
 * tabulation, progress card, fee statement, seat plan) from the existing
 * modules. Read-only.
 */
class PrintService
{
    public function __construct(private FeeService $fees, private SeatPlanService $seatPlans) {}

    public function schoolName(): string
    {
        return SiteSetting::current()->site_name ?: config('app.name');
    }

    private function grade(float $obtained, float $full, bool $absent): array
    {
        if ($absent || $full <= 0) {
            return ['grade' => $absent ? 'Abs' : '—', 'pass' => false];
        }
        $pct = $obtained / $full * 100;
        $g = GradeScale::forPercentage($pct);

        return ['grade' => $g->name ?? '—', 'pass' => (bool) $g];
    }

    /** One student's marksheet for one exam. */
    public function marksheet(int $examId, int $studentId): array
    {
        $exam = Exam::with('type')->findOrFail($examId);
        $student = Student::findOrFail($studentId);
        $record = $student->liveRecord()->with(['schoolClass', 'section'])->first();

        $schedules = ExamSchedule::where('exam_id', $examId)
            ->when($record, fn ($q) => $q->where('class_id', $record->class_id)->where('section_id', $record->section_id))
            ->with('subject')->get();

        $marks = Mark::where('exam_id', $examId)->where('student_id', $studentId)->get()->keyBy('subject_id');

        $rows = $schedules->map(function ($sc) use ($marks) {
            $m = $marks->get($sc->subject_id);
            $obtained = $m ? (float) $m->marks_obtained : 0.0;
            $absent = $m ? (bool) $m->is_absent : true;
            $g = $this->grade($obtained, (float) $sc->full_mark, $absent);

            return [
                'subject'  => optional($sc->subject)->name,
                'full'     => (float) $sc->full_mark,
                'pass'     => (float) $sc->pass_mark,
                'obtained' => $absent ? null : $obtained,
                'grade'    => $g['grade'],
                'is_pass'  => $absent ? false : ($obtained >= (float) $sc->pass_mark),
            ];
        })->all();

        $result = ExamResult::where('exam_id', $examId)->where('student_id', $studentId)->first();

        return [
            'exam'    => $exam,
            'student' => $student,
            'record'  => $record,
            'rows'    => $rows,
            'result'  => $result,
        ];
    }

    /** A whole class/section's result grid for one exam. */
    public function tabulation(int $examId, int $classId, int $sectionId): array
    {
        $exam = Exam::with('type')->findOrFail($examId);
        $subjects = ExamSchedule::where('exam_id', $examId)->where('class_id', $classId)->where('section_id', $sectionId)
            ->with('subject')->get();

        $records = StudentRecord::live()->where('class_id', $classId)->where('section_id', $sectionId)
            ->with(['student', 'schoolClass', 'section'])->orderBy('roll_no')->get()
            ->filter(fn ($r) => $r->student !== null);

        $studentIds = $records->pluck('student_id');
        $marks = Mark::where('exam_id', $examId)->whereIn('student_id', $studentIds)->get()
            ->groupBy('student_id')->map(fn ($g) => $g->keyBy('subject_id'));
        $results = ExamResult::where('exam_id', $examId)->whereIn('student_id', $studentIds)->get()->keyBy('student_id');

        $rows = $records->map(function ($r) use ($subjects, $marks, $results) {
            $mine = $marks->get($r->student_id) ?? collect();
            $cells = [];
            foreach ($subjects as $sc) {
                $m = $mine->get($sc->subject_id);
                $cells[$sc->subject_id] = $m ? ((bool) $m->is_absent ? 'A' : (string) (float) $m->marks_obtained) : '—';
            }
            $res = $results->get($r->student_id);

            return [
                'roll'    => $r->roll_no,
                'student' => $r->student,
                'cells'   => $cells,
                'total'   => $res ? (float) $res->total_marks : null,
                'gpa'     => $res ? (float) $res->gpa : null,
                'grade'   => $res->grade ?? '—',
                'position'=> $res->position ?? null,
            ];
        })->all();

        $first = $records->first();

        return [
            'exam'     => $exam,
            'class'    => optional(optional($first)->schoolClass)->name,
            'section'  => optional(optional($first)->section)->name,
            'subjects' => $subjects,
            'rows'     => $rows,
        ];
    }

    /** A student's term-by-term progress across the year's exams. */
    public function progressCard(int $studentId): array
    {
        $student = Student::findOrFail($studentId);
        $record = $student->liveRecord()->with(['schoolClass', 'section'])->first();

        $results = ExamResult::where('student_id', $studentId)->with('exam.type')->get();
        $terms = $results->map(fn ($r) => [
            'exam'  => optional($r->exam)->name,
            'total' => (float) $r->total_marks,
            'full'  => (float) $r->total_full_mark,
            'gpa'   => (float) $r->gpa,
            'grade' => $r->grade,
            'pass'  => (bool) $r->is_pass,
        ])->all();

        $overall = $results->count() ? round($results->avg('gpa'), 2) : 0.0;

        return ['student' => $student, 'record' => $record, 'terms' => $terms, 'overall_gpa' => $overall];
    }

    /** A student's fee statement / invoice. */
    public function invoice(int $studentId): array
    {
        $student = Student::findOrFail($studentId);
        $record = $student->liveRecord()->with(['schoolClass', 'section'])->first();
        $ledger = $this->fees->studentLedger($studentId);

        $net = 0.0; $paid = 0.0; $due = 0.0;
        foreach ($ledger as $line) {
            $net += $line['balance']['net'];
            $paid += $line['balance']['settled'];
            $due += $line['balance']['due'];
        }

        return [
            'student' => $student,
            'record'  => $record,
            'ledger'  => $ledger,
            'totals'  => ['net' => round($net, 2), 'paid' => round($paid, 2), 'due' => round($due, 2)],
        ];
    }

    /** One room's seating chart for an exam (or every room in the plan when $classroomId is null). */
    public function seatPlan(int $examId, ?int $classroomId = null): array
    {
        $exam = Exam::with('type')->findOrFail($examId);
        $plan = ExamSeatPlan::where('exam_id', $examId)->with('rooms.classroom')->first();

        if (! $plan) {
            return ['exam' => $exam, 'plan' => null, 'rooms' => []];
        }

        $planRooms = $classroomId
            ? $plan->rooms->where('classroom_id', $classroomId)
            : $plan->rooms;

        $rooms = $planRooms->map(fn ($r) => [
            'classroom'   => $r->classroom,
            'assignments' => $this->seatPlans->assignmentsForRoom($plan, $r->classroom_id),
        ])->values()->all();

        return ['exam' => $exam, 'plan' => $plan, 'rooms' => $rooms];
    }
}
