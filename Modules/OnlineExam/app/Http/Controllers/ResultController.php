<?php

namespace Modules\OnlineExam\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\OnlineExam\Models\OnlineExam;
use Modules\OnlineExam\Models\OnlineExamAttempt;
use Modules\OnlineExam\Services\OnlineExamService;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $exams = OnlineExam::latest()->get();

        $selectedExam = null;
        $attempts = null;

        if ($request->filled('exam_id')) {
            $selectedExam = OnlineExam::find($request->input('exam_id'));

            if ($selectedExam) {
                $attempts = $selectedExam->attempts()->with('student')->latest()->get();
            }
        }

        return view('onlineexam::results.index', compact('exams', 'selectedExam', 'attempts'));
    }

    public function show(OnlineExamAttempt $attempt)
    {
        $attempt->load(['exam', 'student', 'answers.question.options']);

        return view('onlineexam::results.show', compact('attempt'));
    }

    public function mark(Request $request, OnlineExamAttempt $attempt, OnlineExamService $service)
    {
        $request->validate([
            'marks'   => ['array'],
            'marks.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        foreach ($request->input('marks', []) as $answerId => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $answer = $attempt->answers()->find($answerId);

            if ($answer) {
                $service->markAnswer($answer, (float) $value, auth()->id());
            }
        }

        return redirect()->route('onlineexam.results.show', $attempt)->with('status', 'Marks saved.');
    }
}
