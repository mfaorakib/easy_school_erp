<?php

namespace Modules\Examination\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Examination\Models\Exam;
use Modules\Examination\Models\ExamType;

class ExamController extends Controller
{
    public function index()
    {
        return view('examination::exams.index', ['exams' => Exam::with('type')->latest()->get()]);
    }

    public function create()
    {
        return view('examination::exams.form', ['exam' => new Exam, 'types' => ExamType::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        Exam::create($this->validated($request));

        return redirect()->route('exam.exams.index')->with('status', 'Exam created.');
    }

    public function edit(Exam $exam)
    {
        return view('examination::exams.form', ['exam' => $exam, 'types' => ExamType::orderBy('name')->get()]);
    }

    public function update(Request $request, Exam $exam)
    {
        $exam->update($this->validated($request));

        return redirect()->route('exam.exams.index')->with('status', 'Exam updated.');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();

        return redirect()->route('exam.exams.index')->with('status', 'Exam deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'         => ['required', 'string', 'max:150'],
            'exam_type_id' => ['nullable', 'integer', 'exists:exam_types,id'],
        ]);
    }
}
