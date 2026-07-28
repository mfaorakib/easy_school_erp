<?php

namespace Modules\AcademicCore\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AcademicCore\Models\DocumentType;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\StudentCategory;
use Modules\AcademicCore\Models\StudentGroup;
use Modules\AcademicCore\Models\Subject;
use Modules\AcademicCore\Services\ClassService;

/** A small starter academic structure so the app is usable on first run. */
class AcademicCoreDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $sections = collect(['A', 'B'])->map(
            fn ($n) => Section::firstOrCreate(['name' => $n])
        );

        $classService = app(ClassService::class);
        foreach (['Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5'] as $i => $name) {
            if (SchoolClass::where('name', $name)->exists()) {
                continue;
            }
            $classService->create(['name' => $name, 'pass_mark' => 33, 'position' => $i + 1], $sections->pluck('id')->all());
        }

        foreach (['English', 'Mathematics', 'Science', 'Social Studies'] as $name) {
            Subject::firstOrCreate(['name' => $name], ['type' => 'theory', 'pass_mark' => 33]);
        }

        foreach (['General', 'Special'] as $name) {
            StudentCategory::firstOrCreate(['name' => $name]);
        }

        foreach (['Science', 'Commerce', 'Arts'] as $name) {
            StudentGroup::firstOrCreate(['name' => $name]);
        }

        $documentTypes = [
            ['name' => 'Birth Certificate', 'is_required' => true, 'sort_order' => 1],
            ['name' => 'Transfer Certificate', 'is_required' => false, 'sort_order' => 2],
            ['name' => 'Character Certificate', 'is_required' => false, 'sort_order' => 3],
        ];
        foreach ($documentTypes as $type) {
            DocumentType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
