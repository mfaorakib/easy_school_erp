<?php

namespace Modules\Homework\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Subject;
use Modules\Homework\Models\Homework;

class HomeworkDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $class   = SchoolClass::where('name', 'Class 1')->first();
        $section = Section::where('name', 'A')->first();
        $subject = Subject::where('name', 'English')->first();

        if ($class && $section && $subject) {
            Homework::firstOrCreate(
                ['title' => 'Essay: My School', 'class_id' => $class->id, 'section_id' => $section->id, 'subject_id' => $subject->id],
                [
                    'description'      => 'Write a 200-word essay about your school.',
                    'homework_date'    => now()->toDateString(),
                    'submission_date'  => now()->addDays(3)->toDateString(),
                    'evaluation_marks' => 10,
                ],
            );
        }
    }
}
