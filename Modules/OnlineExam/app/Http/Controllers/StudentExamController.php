<?php

namespace Modules\OnlineExam\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\Student;
use Modules\OnlineExam\Models\OnlineExam;
use Modules\OnlineExam\Models\OnlineExamAttempt;
use Modules\OnlineExam\Services\OnlineExamService;

class StudentExamController extends Controller
{
    public function index()
    {
        $student = $this->currentStudent();

        if (! $student) {
            return view('onlineexam::student.index', [
                'student'  => null,
                'exams'    => collect(),
                'attempts' => collect(),
            ]);
        }

        $rec = $student->liveRecord()->first();

        $exams = OnlineExam::published()
            ->when($rec, fn ($q) => $q->where('class_id', $rec->class_id)->where('section_id', $rec->section_id))
            ->with(['subject'])
            ->latest()
            ->get();

        $attempts = OnlineExamAttempt::where('student_id', $student->id)
            ->get()
            ->keyBy('online_exam_id');

        return view('onlineexam::student.index', compact('student', 'exams', 'attempts'));
    }

    public function take(OnlineExam $exam, OnlineExamService $service)
    {
        $student = $this->currentStudent();

        if (! $student) {
            return redirect()->route('onlineexam.student.index')->with('status', 'No student account.');
        }

        $attempt = $service->startAttempt($exam, $student);

        if ($attempt->isSubmitted()) {
            return redirect()->route('onlineexam.student.result', $attempt);
        }

        $exam->load('questions.options');

        return view('onlineexam::student.take', compact('exam', 'attempt'));
    }

    public function submit(Request $request, OnlineExam $exam, OnlineExamService $service)
    {
        $student = $this->currentStudent();

        if (! $student) {
            return redirect()->route('onlineexam.student.index')->with('status', 'No student account.');
        }

        $attempt = $service->startAttempt($exam, $student);

        if ($attempt->isSubmitted()) {
            return redirect()->route('onlineexam.student.result', $attempt);
        }

        $raw = $request->input('answers', []);

        $responses = [];
        foreach ($exam->questions as $q) {
            $responses[$q->id] = [
                'options' => (array) data_get($raw, $q->id.'.options', []),
                'bool'    => data_get($raw, $q->id.'.bool'),
                'text'    => data_get($raw, $q->id.'.text'),
            ];
        }

        $service->submitAttempt($attempt, $responses);

        return redirect()->route('onlineexam.student.result', $attempt)->with('status', 'Submitted.');
    }

    public function result(OnlineExamAttempt $attempt)
    {
        $attempt->load(['exam', 'answers.question.options']);

        return view('onlineexam::student.result', compact('attempt'));
    }

    private function currentStudent(): ?Student
    {
        return Student::where('user_id', auth()->id())->first();
    }
}
