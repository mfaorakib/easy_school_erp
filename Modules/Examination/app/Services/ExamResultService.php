<?php

namespace Modules\Examination\Services;

use App\Core\Support\Context;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\AcademicCore\Models\StudentRecord;
use Modules\Examination\Models\ExamResult;
use Modules\Examination\Models\ExamSchedule;
use Modules\Examination\Models\GradeScale;
use Modules\Examination\Models\Mark;

/**
 * Marks entry + result computation.
 *
 * Grade: a subject percentage is looked up in grade_scales (mark_from..mark_upto)
 *        → grade name + GPA point.
 * Result per student (across all scheduled subjects of the exam for their class):
 *   - a subject FAILS if the student is absent or scored below its pass_mark
 *   - if ANY subject fails → overall FAIL: gpa = 0.00, grade = "F"
 *   - else gpa = average of subject GPA points; grade = grade of overall %
 *   - rank within class/section by total marks (passers first)
 */
class ExamResultService
{
    /** Live students of a class/section with their mark for one subject. */
    public function marksRoster(int $examId, int $classId, int $sectionId, int $subjectId): array
    {
        $students = StudentRecord::live()
            ->where('class_id', $classId)->where('section_id', $sectionId)
            ->with('student')->orderBy('roll_no')->get();

        $marks = Mark::where('exam_id', $examId)->where('subject_id', $subjectId)
            ->whereIn('student_id', $students->pluck('student_id'))
            ->get()->keyBy('student_id');

        return $students->map(fn ($r) => [
            'student' => $r->student,
            'roll_no' => $r->roll_no,
            'marks'   => optional($marks->get($r->student_id))->marks_obtained,
            'absent'  => (bool) optional($marks->get($r->student_id))->is_absent,
        ])->filter(fn ($x) => $x['student'])->values()->all();
    }

    /** Save marks for one exam/class/section/subject. $rows = [student_id => ['marks'=>, 'absent'=>]]. */
    public function saveMarks(int $examId, int $classId, int $sectionId, int $subjectId, array $rows): int
    {
        $yearId = Context::academicYearId();

        return DB::transaction(function () use ($examId, $classId, $sectionId, $subjectId, $rows, $yearId) {
            $n = 0;
            foreach ($rows as $studentId => $row) {
                Mark::updateOrCreate(
                    ['exam_id' => $examId, 'student_id' => (int) $studentId, 'subject_id' => $subjectId],
                    [
                        'class_id'         => $classId,
                        'section_id'       => $sectionId,
                        'marks_obtained'   => $row['marks'] ?? 0,
                        'is_absent'        => ! empty($row['absent']),
                        'academic_year_id' => $yearId,
                    ],
                );
                $n++;
            }

            return $n;
        });
    }

    /** Recompute and store results for every live student of a class/section. */
    public function computeResults(int $examId, int $classId, int $sectionId): int
    {
        $yearId    = Context::academicYearId();
        $schedules = ExamSchedule::where('exam_id', $examId)
            ->where('class_id', $classId)->where('section_id', $sectionId)
            ->get()->keyBy('subject_id');

        if ($schedules->isEmpty()) {
            return 0;
        }

        $students = StudentRecord::live()
            ->where('class_id', $classId)->where('section_id', $sectionId)
            ->pluck('student_id')->unique();

        $marks = Mark::where('exam_id', $examId)
            ->whereIn('student_id', $students)
            ->get()->groupBy('student_id');

        $computed = collect();
        foreach ($students as $studentId) {
            $computed->push($this->resultFor($studentId, $schedules, $marks->get($studentId) ?? collect()));
        }

        // Rank by total marks desc (reference default merit-list ordering).
        $ranked = $computed->sortByDesc('total_marks')->values();

        return DB::transaction(function () use ($ranked, $examId, $classId, $sectionId, $yearId) {
            $position = 0;
            foreach ($ranked as $r) {
                $position++;
                ExamResult::updateOrCreate(
                    ['exam_id' => $examId, 'student_id' => $r['student_id']],
                    [
                        'class_id'        => $classId,
                        'section_id'      => $sectionId,
                        'total_marks'     => $r['total_marks'],
                        'total_full_mark' => $r['total_full_mark'],
                        'gpa'             => $r['gpa'],
                        'grade'           => $r['grade'],
                        'is_pass'         => $r['is_pass'],
                        'position'        => $position,
                        'academic_year_id'=> $yearId,
                    ],
                );
            }

            return $ranked->count();
        });
    }

    /** @param Collection<int,ExamSchedule> $schedules  @param Collection<int,Mark> $studentMarks */
    private function resultFor(int $studentId, Collection $schedules, Collection $studentMarks): array
    {
        $marksBySubject = $studentMarks->keyBy('subject_id');
        $totalMarks = 0.0;
        $totalFull  = 0.0;
        $points     = [];
        $failed     = false;

        foreach ($schedules as $subjectId => $schedule) {
            $full = (float) $schedule->full_mark;
            $pass = (float) $schedule->pass_mark;
            $mark = $marksBySubject->get($subjectId);

            $obtained = $mark ? (float) $mark->marks_obtained : 0.0;
            $absent   = $mark ? (bool) $mark->is_absent : true; // no mark entered = not sat

            $totalMarks += $absent ? 0 : $obtained;
            $totalFull  += $full;

            if ($absent || $obtained < $pass) {
                $failed   = true;
                $points[] = 0.0;
            } else {
                $pct      = $full > 0 ? $obtained / $full * 100 : 0;
                $points[] = (float) (GradeScale::forPercentage($pct)->gpa ?? 0);
            }
        }

        // Overall GPA = simple average of subject GPA points; overall grade comes
        // from the GPA band (reference behaviour), not the raw percentage.
        $gpa   = $failed ? 0.0 : round(array_sum($points) / max(count($points), 1), 2);
        $grade = $failed ? 'F' : (GradeScale::forGpa($gpa)->name ?? null);

        return [
            'student_id'      => $studentId,
            'total_marks'     => round($totalMarks, 2),
            'total_full_mark' => round($totalFull, 2),
            'gpa'             => $gpa,
            'grade'           => $grade,
            'is_pass'         => ! $failed,
        ];
    }
}
