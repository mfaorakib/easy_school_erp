<?php

namespace Modules\AcademicCore\Services;

use App\Core\Support\Context;
use Illuminate\Support\Facades\DB;
use Modules\AcademicCore\Models\StudentRecord;
use Modules\Foundation\Services\SettingService;

/**
 * Central enrollment routine (the reference system insertStudentRecord). Reused by admission
 * and promotion. Creates/updates ONE student_records row and enforces:
 *  - single default enrollment per student per year
 *  - single-roll-per-year vs per-record roll (multiple_roll setting)
 *  - roll auto-assignment (max in the target slot + 1) when none supplied
 *
 * All cross-year reads bypass the active-year global scope (allYears()).
 */
class EnrollmentService
{
    public function __construct(private readonly SettingService $settings) {}

    public function enroll(
        int $studentId,
        int $classId,
        int $sectionId,
        ?int $yearId = null,
        ?int $rollNo = null,
        bool $isDefault = true,
        bool $isPromote = false,
        ?int $recordId = null,
    ): StudentRecord {
        $yearId ??= Context::academicYearId();

        return DB::transaction(function () use ($studentId, $classId, $sectionId, $yearId, $rollNo, $isDefault, $isPromote, $recordId) {
            // Roll number: keep supplied, else auto = max in the target slot + 1.
            $rollNo ??= (int) StudentRecord::allYears()
                ->where('academic_year_id', $yearId)
                ->where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->max('roll_no') + 1;

            // Single default per student per year.
            if ($isDefault) {
                StudentRecord::allYears()
                    ->where('student_id', $studentId)
                    ->where('academic_year_id', $yearId)
                    ->when($recordId, fn ($q) => $q->whereKeyNot($recordId))
                    ->update(['is_default' => false]);
            }

            // Single LIVE enrollment (is_promote = 0) per student per year. When
            // this row is the live one, demote any other live record for the same
            // (student, year) to historical first — e.g. a student pre-admitted a
            // year ahead who is now promoted into a different class in that year.
            // Scoped to $yearId, so records in OTHER years (promotion history) are
            // untouched; excludes the record being updated so re-enrolling is a
            // no-op.
            if (! $isPromote) {
                StudentRecord::allYears()
                    ->where('student_id', $studentId)
                    ->where('academic_year_id', $yearId)
                    ->where('is_promote', false)
                    ->when($recordId, fn ($q) => $q->whereKeyNot($recordId))
                    ->update(['is_promote' => true]);
            }

            $attributes = [
                'student_id'       => $studentId,
                'class_id'         => $classId,
                'section_id'       => $sectionId,
                'academic_year_id' => $yearId,
                'roll_no'          => $rollNo,
                'is_default'       => $isDefault,
                'is_promote'       => $isPromote,
            ];

            $record = $recordId
                ? tap(StudentRecord::allYears()->findOrFail($recordId))->update($attributes)
                : StudentRecord::create($attributes);

            // Single-roll mode: one roll number across all of the student's
            // records in this year.
            if (! $this->settings->get('multiple_roll', false)) {
                StudentRecord::allYears()
                    ->where('student_id', $studentId)
                    ->where('academic_year_id', $yearId)
                    ->update(['roll_no' => $rollNo]);
            }

            return $record;
        });
    }
}
