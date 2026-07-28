<?php

namespace Modules\AcademicCore\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\AcademicCore\Models\Student;
use Modules\AcademicCore\Models\StudentPromotion;
use Modules\AcademicCore\Models\StudentRecord;

/**
 * Student promotion (the reference system SmStudentPromoteController::promote).
 *
 * Net effect: promotion NEVER edits the old enrollment's class. It LAYERS a new
 * student_records row for the target year, flips the old record is_promote = 1,
 * and writes an auditable student_promotions snapshot. Class/session history is
 * therefore fully reconstructable.
 *
 * @param array<int,array{student_id:int,class_id:int,section_id:?int,roll_no:?int,result:?string}> $items
 * @return array{promoted:int,graduated:int,skipped:int}
 */
class PromotionService
{
    public function __construct(private readonly EnrollmentService $enrollment) {}

    public function promote(int $fromYearId, int $toYearId, array $items, bool $graduateAll = false): array
    {
        $result = ['promoted' => 0, 'graduated' => 0, 'skipped' => 0];

        DB::transaction(function () use ($fromYearId, $toYearId, $items, $graduateAll, &$result) {
            foreach ($items as $item) {
                if (empty($item['student_id'])) {
                    continue;
                }

                $isGraduation = $graduateAll || empty($item['section_id']);

                if ($isGraduation) {
                    $this->graduate((int) $item['student_id'], $fromYearId)
                        ? $result['graduated']++
                        : $result['skipped']++;
                    continue;
                }

                $outcome = $this->promoteOne(
                    (int) $item['student_id'],
                    (int) $item['class_id'],
                    (int) $item['section_id'],
                    $fromYearId,
                    $toYearId,
                    $item['roll_no'] ?? null,
                    $item['result'] ?? 'P',
                );

                $outcome ? $result['promoted']++ : $result['skipped']++;
            }
        });

        return $result;
    }

    private function promoteOne(int $studentId, int $classId, int $sectionId, int $fromYearId, int $toYearId, ?int $rollNo, string $result): bool
    {
        // 1. Duplicate check — already promoted into the target slot?
        $already = StudentRecord::allYears()
            ->where('student_id', $studentId)
            ->where('academic_year_id', $toYearId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('is_promote', false)
            ->exists();

        if ($already) {
            return false;
        }

        // 2. Roll clash when a roll was explicitly supplied.
        if ($rollNo !== null) {
            $taken = StudentRecord::allYears()
                ->where('academic_year_id', $toYearId)
                ->where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->where('roll_no', $rollNo)
                ->exists();

            if ($taken) {
                throw ValidationException::withMessages([
                    'roll_no' => "Roll no {$rollNo} already exists in the target class/section.",
                ]);
            }
        }

        // 3. Previous live enrollment.
        $preRecord = StudentRecord::allYears()
            ->where('student_id', $studentId)
            ->where('academic_year_id', $fromYearId)
            ->where('is_promote', false)
            ->first();

        $student = Student::find($studentId);

        // 4. Audit snapshot.
        StudentPromotion::create([
            'student_id'          => $studentId,
            'previous_class_id'   => $preRecord?->class_id,
            'current_class_id'    => $classId,
            'previous_section_id' => $preRecord?->section_id,
            'current_section_id'  => $sectionId,
            'previous_year_id'    => $fromYearId,
            'current_year_id'     => $toYearId,
            'admission_no'        => $student?->admission_no,
            'previous_roll_no'    => $preRecord?->roll_no,
            'current_roll_no'     => $rollNo,
            'student_info'        => $student?->only(['id', 'full_name', 'admission_no']),
            'result_status'       => $result,
            'academic_year_id'    => $toYearId,
        ]);

        // 5. New enrolment for the target year (live).
        $this->enrollment->enroll(
            studentId: $studentId,
            classId:   $classId,
            sectionId: $sectionId,
            yearId:    $toYearId,
            rollNo:    $rollNo,
            isDefault: true,
            isPromote: false,
        );

        // 6. Old record becomes historical.
        $preRecord?->update(['is_promote' => true]);

        return true;
    }

    /** Graduation branch: flag the live record, no new enrolment. */
    private function graduate(int $studentId, int $fromYearId): bool
    {
        $record = StudentRecord::allYears()
            ->where('student_id', $studentId)
            ->where('academic_year_id', $fromYearId)
            ->where('is_promote', false)
            ->first();

        if (! $record) {
            return false;
        }

        $record->update(['is_graduate' => true, 'is_promote' => true]);

        return true;
    }
}
