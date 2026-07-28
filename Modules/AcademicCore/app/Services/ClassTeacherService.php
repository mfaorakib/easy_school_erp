<?php

namespace Modules\AcademicCore\Services;

use App\Core\Support\Context;
use Illuminate\Support\Facades\DB;
use Modules\AcademicCore\Models\ClassTeacherAssignment;
use RuntimeException;

/**
 * Class-teacher slots (the reference system SmAssignClassTeacherController). One active slot
 * per class+section+year; one-to-many teachers under it.
 */
class ClassTeacherService
{
    /** @param int[] $teacherIds */
    public function assign(int $classId, int $sectionId, array $teacherIds): ClassTeacherAssignment
    {
        $exists = ClassTeacherAssignment::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('is_active', true)
            ->exists();

        if ($exists) {
            throw new RuntimeException('Class Teacher already assigned.');
        }

        return DB::transaction(function () use ($classId, $sectionId, $teacherIds) {
            $slot = ClassTeacherAssignment::create([
                'class_id'   => $classId,
                'section_id' => $sectionId,
                'is_active'  => true,
            ]);

            $this->syncTeachers($slot, $teacherIds);

            return $slot;
        });
    }

    /** @param int[] $teacherIds */
    public function update(ClassTeacherAssignment $slot, int $classId, int $sectionId, array $teacherIds): ClassTeacherAssignment
    {
        // Another slot must not already own this class+section for the year.
        $clash = ClassTeacherAssignment::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->whereKeyNot($slot->id)
            ->exists();

        if ($clash) {
            throw new RuntimeException('Class Teacher already assigned.');
        }

        return DB::transaction(function () use ($slot, $classId, $sectionId, $teacherIds) {
            $slot->update(['class_id' => $classId, 'section_id' => $sectionId]);

            // Delete members, re-create (supports multiple teachers).
            $slot->members()->delete();
            $this->syncTeachers($slot, $teacherIds);

            return $slot;
        });
    }

    public function delete(ClassTeacherAssignment $slot): void
    {
        DB::transaction(function () use ($slot) {
            $slot->members()->delete();
            $slot->delete();
        });
    }

    /** @param int[] $teacherIds */
    private function syncTeachers(ClassTeacherAssignment $slot, array $teacherIds): void
    {
        foreach (array_unique($teacherIds) as $teacherId) {
            $slot->members()->create(['teacher_id' => $teacherId]);
        }
    }
}
