<?php

namespace Modules\Attendance\Services;

use App\Core\Support\Context;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\AcademicCore\Models\StudentRecord;
use Modules\Attendance\Models\StaffAttendance;
use Modules\Attendance\Models\StudentAttendance;
use Modules\HumanResource\Models\Staff;

/**
 * Take & read attendance. The roster is always the LIVE enrolment
 * (student_records where is_promote = 0) for the active academic year, so
 * promoted/left students never appear. Saving a day upserts one row per
 * student, so re-saving edits rather than duplicates.
 */
class AttendanceService
{
    /**
     * Live students of a class/section with their status for a given date.
     * @return Collection<int,array{student:\Modules\AcademicCore\Models\Student, status:?string, note:?string}>
     */
    public function studentRoster(int $classId, int $sectionId, string $date, ?int $subjectId = null): Collection
    {
        $records = StudentRecord::live()
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->with('student')
            ->orderBy('roll_no')
            ->get();

        $existing = StudentAttendance::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('date', $date)
            ->where('subject_id', $subjectId)
            ->get()
            ->keyBy('student_id');

        return $records->map(fn ($r) => [
            'student' => $r->student,
            'roll_no' => $r->roll_no,
            'status'  => optional($existing->get($r->student_id))->status?->value,
            'note'    => optional($existing->get($r->student_id))->note,
        ])->filter(fn ($row) => $row['student'] !== null)->values();
    }

    /**
     * Save a class/section day. $statuses = [student_id => code].
     * @return int number of rows written
     */
    public function saveStudentAttendance(int $classId, int $sectionId, string $date, array $statuses, array $notes = [], ?int $subjectId = null): int
    {
        $yearId = Context::academicYearId();

        return DB::transaction(function () use ($classId, $sectionId, $date, $statuses, $notes, $subjectId, $yearId) {
            $count = 0;
            foreach ($statuses as $studentId => $code) {
                StudentAttendance::updateOrCreate(
                    ['student_id' => (int) $studentId, 'date' => $date, 'subject_id' => $subjectId],
                    [
                        'class_id'         => $classId,
                        'section_id'       => $sectionId,
                        'academic_year_id' => $yearId,
                        'status'           => $code,
                        'note'             => $notes[$studentId] ?? null,
                    ],
                );
                $count++;
            }

            return $count;
        });
    }

    /**
     * One student's own attendance history (latest first) plus a simple summary.
     * @return array{rows: Collection, present: int, total: int, percent: float}
     */
    public function historyForStudent(int $studentId): array
    {
        $rows = StudentAttendance::where('student_id', $studentId)
            ->orderByDesc('date')
            ->get();

        $total = $rows->count();
        $present = $rows->filter(fn ($r) => $r->status?->isPresent())->count();

        return [
            'rows'    => $rows,
            'present' => $present,
            'total'   => $total,
            'percent' => $total > 0 ? round($present / $total * 100, 1) : 0.0,
        ];
    }

    /** Active staff with their status for a date. */
    public function staffRoster(string $date): Collection
    {
        $staff = Staff::where('is_active', true)->with('user')->orderBy('full_name')->get();

        $existing = StaffAttendance::where('date', $date)->get()->keyBy('staff_id');

        return $staff->map(fn ($s) => [
            'staff'  => $s,
            'status' => optional($existing->get($s->id))->status?->value,
            'note'   => optional($existing->get($s->id))->note,
        ]);
    }

    /** Save staff day. $statuses = [staff_id => code]. */
    public function saveStaffAttendance(string $date, array $statuses, array $notes = []): int
    {
        return DB::transaction(function () use ($date, $statuses, $notes) {
            $count = 0;
            foreach ($statuses as $staffId => $code) {
                StaffAttendance::updateOrCreate(
                    ['staff_id' => (int) $staffId, 'date' => $date],
                    ['status' => $code, 'note' => $notes[$staffId] ?? null],
                );
                $count++;
            }

            return $count;
        });
    }
}
