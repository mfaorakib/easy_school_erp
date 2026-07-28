<?php

namespace Modules\AcademicCore\Services;

use Illuminate\Support\Facades\DB;
use Modules\AcademicCore\Models\ClassSection;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\StudentRecord;
use Modules\AcademicCore\Models\SubjectAssignment;
use RuntimeException;

/**
 * Class + its section links (the reference system SmClassController). Update is a destructive
 * re-sync of class_sections (delete all, re-insert) — not a diff — matching the
 * original behaviour exactly.
 */
class ClassService
{
    /** @param int[] $sectionIds */
    public function create(array $data, array $sectionIds): SchoolClass
    {
        return DB::transaction(function () use ($data, $sectionIds) {
            $class = SchoolClass::create([
                'name'      => $data['name'],
                'pass_mark' => $data['pass_mark'] ?? null,
                'position'  => $data['position'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $this->syncSections($class, $sectionIds);

            return $class;
        });
    }

    /** @param int[] $sectionIds */
    public function update(SchoolClass $class, array $data, array $sectionIds): SchoolClass
    {
        return DB::transaction(function () use ($class, $data, $sectionIds) {
            $class->update([
                'name'      => $data['name'] ?? $class->name,
                'pass_mark' => $data['pass_mark'] ?? $class->pass_mark,
                'position'  => $data['position'] ?? $class->position,
                'is_active' => $data['is_active'] ?? $class->is_active,
            ]);

            // Destructive re-sync (the reference system deletes all pivot rows first).
            $class->classSections()->delete();
            $this->syncSections($class, $sectionIds);

            return $class;
        });
    }

    public function delete(SchoolClass $class): void
    {
        if ($this->isReferenced($class)) {
            throw new RuntimeException('Class is in use and cannot be deleted.');
        }

        DB::transaction(function () use ($class) {
            $class->classSections()->delete();
            $class->delete();
        });
    }

    /** @param int[] $sectionIds */
    private function syncSections(SchoolClass $class, array $sectionIds): void
    {
        foreach (array_unique($sectionIds) as $sectionId) {
            ClassSection::create([
                'class_id'   => $class->id,
                'section_id' => $sectionId,
            ]);
        }
    }

    private function isReferenced(SchoolClass $class): bool
    {
        return StudentRecord::allYears()->where('class_id', $class->id)->exists()
            || SubjectAssignment::allYears()->where('class_id', $class->id)->exists();
    }
}
