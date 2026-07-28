<?php

namespace Modules\Examination\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Subject;
use Modules\Examination\Models\Exam;
use Modules\Examination\Models\ExamSchedule;
use Modules\Examination\Models\ExamType;
use Modules\Examination\Models\GradeScale;

/** Default grading scale + a starter exam so the module is usable on first run. */
class ExaminationDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $scale = [
            ['A+', 5.00, 80, 100],
            ['A',  4.00, 70, 79],
            ['A-', 3.50, 60, 69],
            ['B',  3.00, 50, 59],
            ['C',  2.00, 40, 49],
            ['D',  1.00, 33, 39],
            ['F',  0.00, 0, 32],
        ];

        foreach ($scale as [$name, $gpa, $from, $upto]) {
            GradeScale::firstOrCreate(
                ['name' => $name],
                ['gpa' => $gpa, 'mark_from' => $from, 'mark_upto' => $upto],
            );
        }

        $type = ExamType::firstOrCreate(['name' => 'Final']);
        ExamType::firstOrCreate(['name' => 'Midterm']);
        $exam = Exam::firstOrCreate(['name' => 'Final Exam'], ['exam_type_id' => $type->id]);

        // A starter schedule so the exam has a class/section to draw students
        // from — needed for both marks entry and the seat plan demo.
        $class = SchoolClass::orderBy('position')->first();
        $section = Section::first();
        $subject = Subject::first();

        if ($class && $section && $subject) {
            ExamSchedule::firstOrCreate(
                ['exam_id' => $exam->id, 'class_id' => $class->id, 'section_id' => $section->id, 'subject_id' => $subject->id],
                ['exam_date' => now()->addWeek()->toDateString(), 'start_time' => '09:00', 'end_time' => '12:00', 'full_mark' => 100, 'pass_mark' => 33],
            );
        }
    }
}
