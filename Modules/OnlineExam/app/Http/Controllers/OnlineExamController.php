<?php

namespace Modules\OnlineExam\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Subject;
use Modules\OnlineExam\Models\OnlineExam;
use Modules\OnlineExam\Models\QuestionBank;
use Modules\OnlineExam\Services\OnlineExamService;

class OnlineExamController extends Controller
{
    public function index()
    {
        $exams = OnlineExam::with(['schoolClass', 'section', 'subject'])
            ->withCount('questions')
            ->latest()
            ->get();

        return view('onlineexam::exams.index', compact('exams'));
    }

    public function create()
    {
        return view('onlineexam::exams.form', $this->formData(new OnlineExam));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['auto_mark'] = $request->boolean('auto_mark');
        $data['is_published'] = $request->boolean('is_published');
        $data['created_by'] = auth()->id();

        OnlineExam::create($data);

        return redirect()->route('onlineexam.exams.index')->with('status', 'Exam created.');
    }

    public function edit(OnlineExam $exam)
    {
        return view('onlineexam::exams.form', $this->formData($exam));
    }

    public function update(Request $request, OnlineExam $exam)
    {
        $data = $this->validated($request);
        $data['auto_mark'] = $request->boolean('auto_mark');
        $data['is_published'] = $request->boolean('is_published');

        $exam->update($data);

        return redirect()->route('onlineexam.exams.index')->with('status', 'Exam updated.');
    }

    public function destroy(OnlineExam $exam)
    {
        $exam->delete();

        return redirect()->route('onlineexam.exams.index')->with('status', 'Exam deleted.');
    }

    public function questions(OnlineExam $exam)
    {
        $available = QuestionBank::active()
            ->with('group')
            ->where(function ($q) use ($exam) {
                $q->where('class_id', $exam->class_id)->orWhereNull('class_id');
            })
            ->latest()
            ->get();

        $assignedIds = $exam->questions()->pluck('question_banks.id')->all();

        return view('onlineexam::exams.questions', compact('exam', 'available', 'assignedIds'));
    }

    public function syncQuestions(Request $request, OnlineExam $exam, OnlineExamService $service)
    {
        $request->validate([
            'question_ids'   => ['array'],
            'question_ids.*' => ['exists:question_banks,id'],
        ]);

        $service->assignQuestions($exam, $request->input('question_ids', []));

        return redirect()->route('onlineexam.exams.questions', $exam)->with('status', 'Questions updated.');
    }

    private function formData(OnlineExam $exam): array
    {
        return [
            'exam'     => $exam,
            'classes'  => SchoolClass::active()->orderBy('name')->get(),
            'sections' => Section::active()->orderBy('name')->get(),
            'subjects' => Subject::active()->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'class_id'         => ['required', 'exists:classes,id'],
            'section_id'       => ['required', 'exists:sections,id'],
            'subject_id'       => ['nullable', 'exists:subjects,id'],
            'title'            => ['required', 'string', 'max:255'],
            'exam_date'        => ['nullable', 'date'],
            'start_time'       => ['nullable'],
            'end_time'         => ['nullable'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'instruction'      => ['nullable', 'string'],
        ]);
    }
}
