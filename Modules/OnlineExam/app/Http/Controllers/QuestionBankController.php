<?php

namespace Modules\OnlineExam\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\OnlineExam\Models\QuestionBank;
use Modules\OnlineExam\Models\QuestionGroup;

class QuestionBankController extends Controller
{
    public function index()
    {
        $questions = QuestionBank::with(['group', 'schoolClass', 'section'])->latest()->get();

        return view('onlineexam::questions.index', compact('questions'));
    }

    public function create()
    {
        return view('onlineexam::questions.form', $this->formData(new QuestionBank));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($error = $this->mcqError($request, $data)) {
            return $error;
        }

        $question = new QuestionBank;
        $this->fillModel($question, $request, $data);
        $question->is_active = true;
        $question->created_by = auth()->id();
        $question->save();

        $this->syncOptions($question, $request, $data);

        return redirect()->route('onlineexam.questions.index')->with('status', 'Question created.');
    }

    public function edit(QuestionBank $question)
    {
        $question->load('options');

        return view('onlineexam::questions.form', $this->formData($question));
    }

    public function update(Request $request, QuestionBank $question)
    {
        $data = $this->validated($request);

        if ($error = $this->mcqError($request, $data)) {
            return $error;
        }

        $this->fillModel($question, $request, $data);
        $question->save();

        $this->syncOptions($question, $request, $data);

        return redirect()->route('onlineexam.questions.index')->with('status', 'Question updated.');
    }

    public function destroy(QuestionBank $question)
    {
        $question->delete();

        return redirect()->route('onlineexam.questions.index')->with('status', 'Question deleted.');
    }

    private function formData(QuestionBank $question): array
    {
        return [
            'question' => $question,
            'groups'   => QuestionGroup::active()->orderBy('title')->get(),
            'classes'  => SchoolClass::active()->orderBy('name')->get(),
            'sections' => Section::active()->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'question_group_id' => ['nullable', 'exists:question_groups,id'],
            'class_id'          => ['nullable', 'exists:classes,id'],
            'section_id'        => ['nullable', 'exists:sections,id'],
            'type'              => ['required', 'in:mcq,truefalse,fill'],
            'difficulty'        => ['required', 'in:easy,medium,hard'],
            'question'          => ['required', 'string'],
            'marks'             => ['required', 'numeric', 'min:0'],
            'options'           => ['array'],
            'options.*'         => ['nullable', 'string', 'max:500'],
            'correct'           => ['array'],
            'correct_bool'      => ['nullable', 'in:0,1'],
            'answer_text'       => ['nullable', 'string'],
        ]);
    }

    /**
     * Validate MCQ requirements. Returns a redirect response on failure, or null when OK.
     */
    private function mcqError(Request $request, array $data)
    {
        if ($data['type'] !== 'mcq') {
            return null;
        }

        $options = array_filter($data['options'] ?? [], fn ($o) => trim((string) $o) !== '');
        $correct = $request->input('correct', []);

        if (count($options) < 2 || count($correct) < 1) {
            return back()->withInput()->withErrors([
                'options' => 'MCQ needs at least 2 options and one correct answer.',
            ]);
        }

        return null;
    }

    /**
     * Fill only the model's own columns, normalized by type.
     */
    private function fillModel(QuestionBank $question, Request $request, array $data): void
    {
        $question->question_group_id = $data['question_group_id'] ?? null;
        $question->class_id          = $data['class_id'] ?? null;
        $question->section_id        = $data['section_id'] ?? null;
        $question->type              = $data['type'];
        $question->difficulty        = $data['difficulty'];
        $question->question          = $data['question'];
        $question->marks             = $data['marks'];

        switch ($data['type']) {
            case 'mcq':
                $question->correct_bool = null;
                $question->answer_text  = null;
                break;
            case 'truefalse':
                $question->correct_bool = (bool) ((int) $request->input('correct_bool'));
                $question->answer_text  = null;
                break;
            case 'fill':
                $question->correct_bool = null;
                $question->answer_text  = $data['answer_text'] ?? null;
                break;
        }
    }

    private function syncOptions(QuestionBank $question, Request $request, array $data): void
    {
        $question->options()->delete();

        if ($data['type'] !== 'mcq') {
            return;
        }

        $correct = $request->input('correct', []);

        foreach ($data['options'] ?? [] as $i => $title) {
            if (trim((string) $title) === '') {
                continue;
            }

            $question->options()->create([
                'title'      => trim((string) $title),
                'is_correct' => in_array((string) $i, $correct, true),
            ]);
        }
    }
}
