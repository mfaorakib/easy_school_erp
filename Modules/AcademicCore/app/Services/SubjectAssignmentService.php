<?php

namespace Modules\AcademicCore\Services;

use Illuminate\Support\Facades\DB;
use Modules\AcademicCore\Models\ClassSection;
use Modules\AcademicCore\Models\SubjectAssignment;

/**
 * Assign subjects (+teacher) to a class/section (the reference system assignSubjectStore).
 *  - section given + replace: delete existing for class+section, re-insert
 *  - no section: broadcast one row per section of the class (append)
 *
 * @param array<int,array{subject_id:int, teacher_id:int|null}> $pairs
 */
class SubjectAssignmentService
{
    public function assign(int $classId, ?int $sectionId, array $pairs, bool $replace = true): void
    {
        DB::transaction(function () use ($classId, $sectionId, $pairs, $replace) {
            if ($sectionId === null) {
                // Broadcast to every section of the class (append, per original).
                $sectionIds = ClassSection::where('class_id', $classId)->pluck('section_id');
                foreach ($sectionIds as $sid) {
                    $this->insertPairs($classId, $sid, $pairs);
                }
                return;
            }

            if ($replace) {
                SubjectAssignment::where('class_id', $classId)
                    ->where('section_id', $sectionId)
                    ->delete();
            }

            $this->insertPairs($classId, $sectionId, $pairs);
        });
    }

    private function insertPairs(int $classId, int $sectionId, array $pairs): void
    {
        foreach ($pairs as $pair) {
            SubjectAssignment::create([
                'class_id'   => $classId,
                'section_id' => $sectionId,
                'subject_id' => $pair['subject_id'],
                'teacher_id' => $pair['teacher_id'] ?? null,
            ]);
        }
    }
}
