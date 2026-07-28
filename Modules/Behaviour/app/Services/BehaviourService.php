<?php

namespace Modules\Behaviour\Services;

use Illuminate\Support\Facades\Auth;
use Modules\AcademicCore\Models\Student;
use Modules\Behaviour\Models\BehaviourRecord;
use Modules\Behaviour\Models\BehaviourType;

/**
 * Behaviour recording. A record snapshots the type's point at the time it is
 * logged (positive = reward, negative = penalty). Per-student totals are the
 * sum of their record points for the active academic year.
 */
class BehaviourService
{
    public function record(int $studentId, int $typeId, string $date, ?string $comment = null): BehaviourRecord
    {
        $type = BehaviourType::findOrFail($typeId);

        return BehaviourRecord::create([
            'student_id'        => $studentId,
            'behaviour_type_id' => $type->id,
            'incident_date'     => $date,
            'comment'           => $comment,
            'points'            => $type->point,
            'created_by'        => Auth::id(),
        ]);
    }

    /**
     * Per-student point totals for the active year (only students with records).
     * @return array<int,array{student:Student, total:float, count:int}>
     */
    public function studentTotals(): array
    {
        $grouped = BehaviourRecord::query()
            ->selectRaw('student_id, SUM(points) as total, COUNT(*) as cnt')
            ->groupBy('student_id')
            ->get();

        $students = Student::whereIn('id', $grouped->pluck('student_id'))->get()->keyBy('id');

        return $grouped->map(fn ($row) => [
            'student' => $students->get($row->student_id),
            'total'   => (float) $row->total,
            'count'   => (int) $row->cnt,
        ])->filter(fn ($x) => $x['student'])
          ->sortByDesc('total')->values()->all();
    }
}
