<?php

namespace Modules\Lesson\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Subject;
use Modules\Lesson\Models\Lesson;

class LessonDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $class   = SchoolClass::where('name', 'Class 1')->first();
        $section = Section::where('name', 'A')->first();
        $subject = Subject::where('name', 'English')->first();

        if ($class && $section && $subject) {
            $lesson = Lesson::firstOrCreate(
                ['title' => 'Chapter 1: Grammar Basics', 'class_id' => $class->id, 'section_id' => $section->id, 'subject_id' => $subject->id],
                ['description' => 'Nouns, verbs and sentence structure.', 'lesson_date' => now()->toDateString()],
            );

            foreach (['Nouns', 'Verbs', 'Sentence structure'] as $i => $t) {
                $lesson->topics()->firstOrCreate(['title' => $t], ['position' => $i + 1]);
            }
        }
    }
}
