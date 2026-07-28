<?php

namespace Modules\OnlineExam\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Subject;
use Modules\OnlineExam\Models\OnlineExam;
use Modules\OnlineExam\Models\QuestionBank;
use Modules\OnlineExam\Models\QuestionGroup;
use Modules\OnlineExam\Services\OnlineExamService;

class OnlineExamDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $class   = SchoolClass::where('name', 'Class 1')->first();
        $section = Section::where('name', 'A')->first();
        $subject = Subject::where('name', 'English')->first();

        if (! $class || ! $section) {
            return;
        }

        $group = QuestionGroup::firstOrCreate(['title' => 'General Knowledge']);

        // MCQ (exactly one correct)
        $mcq = QuestionBank::firstOrCreate(
            ['question' => 'What is the capital of Bangladesh?', 'type' => QuestionBank::TYPE_MCQ],
            ['question_group_id' => $group->id, 'class_id' => $class->id, 'marks' => 2],
        );
        if ($mcq->options()->count() === 0) {
            foreach ([['Dhaka', true], ['Chittagong', false], ['Khulna', false], ['Sylhet', false]] as [$title, $correct]) {
                $mcq->options()->create(['title' => $title, 'is_correct' => $correct]);
            }
        }

        // True / False
        QuestionBank::firstOrCreate(
            ['question' => 'The sun rises in the east.', 'type' => QuestionBank::TYPE_TRUEFALSE],
            ['question_group_id' => $group->id, 'class_id' => $class->id, 'marks' => 1, 'correct_bool' => true],
        );

        // Fill in the blank (manual marking)
        QuestionBank::firstOrCreate(
            ['question' => 'Water freezes at ___ degrees Celsius.', 'type' => QuestionBank::TYPE_FILL],
            ['question_group_id' => $group->id, 'class_id' => $class->id, 'marks' => 2, 'answer_text' => '0'],
        );

        $exam = OnlineExam::firstOrCreate(
            ['title' => 'English — Weekly Quiz 1', 'class_id' => $class->id, 'section_id' => $section->id],
            [
                'subject_id'   => optional($subject)->id,
                'exam_date'    => now()->toDateString(),
                'start_time'   => '10:00',
                'end_time'     => '10:30',
                'duration_minutes' => 30,
                'instruction'  => 'Answer all questions. No negative marking.',
                'auto_mark'    => true,
                'is_published' => true,
            ],
        );

        app(OnlineExamService::class)->assignQuestions(
            $exam,
            QuestionBank::whereIn('type', [QuestionBank::TYPE_MCQ, QuestionBank::TYPE_TRUEFALSE, QuestionBank::TYPE_FILL])
                ->pluck('id')->all(),
        );
    }
}
