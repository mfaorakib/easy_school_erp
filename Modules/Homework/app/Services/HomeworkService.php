<?php

namespace Modules\Homework\Services;

use Illuminate\Support\Facades\DB;
use Modules\AcademicCore\Models\StudentRecord;
use Modules\Homework\Models\Homework;
use Modules\Homework\Models\HomeworkStudent;

/**
 * Homework evaluation. The evaluation roster is the LIVE enrolment of the
 * homework's class/section (student_records, is_promote=0), so it always
 * reflects the current students. Evaluations upsert one row per student.
 */
class HomeworkService
{
    /** @return array<int,array{student:\Modules\AcademicCore\Models\Student, roll_no:?int, eval:?HomeworkStudent}> */
    public function evaluationRoster(Homework $homework): array
    {
        $records = StudentRecord::live()
            ->where('class_id', $homework->class_id)
            ->where('section_id', $homework->section_id)
            ->with('student')->orderBy('roll_no')->get();

        $evals = $homework->students()->get()->keyBy('student_id');

        return $records->map(fn ($r) => [
            'student' => $r->student,
            'roll_no' => $r->roll_no,
            'eval'    => $evals->get($r->student_id),
        ])->filter(fn ($x) => $x['student'])->values()->all();
    }

    /** Save per-student evaluation. $rows = [student_id => ['complete'=>bool,'marks'=>?,'comment'=>?]]. */
    public function saveEvaluation(Homework $homework, array $rows): int
    {
        return DB::transaction(function () use ($homework, $rows) {
            $n = 0;
            foreach ($rows as $studentId => $row) {
                HomeworkStudent::updateOrCreate(
                    ['homework_id' => $homework->id, 'student_id' => (int) $studentId],
                    [
                        'is_complete'    => ! empty($row['complete']),
                        'obtained_marks' => $row['marks'] ?? null,
                        'comment'        => $row['comment'] ?? null,
                        'evaluated_on'   => now()->toDateString(),
                    ],
                );
                $n++;
            }

            return $n;
        });
    }
}
