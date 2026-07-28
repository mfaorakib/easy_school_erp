<?php

namespace Modules\Homework\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Subject;
use Modules\Homework\Models\Homework;
use Modules\HumanResource\Models\Staff;

class HomeworkController extends Controller
{
    public function index()
    {
        $homeworks = Homework::with(['schoolClass', 'section', 'subject'])->latest()->get();

        return view('homework::homeworks.index', compact('homeworks'));
    }

    public function create()
    {
        return view('homework::homeworks.form', $this->formData(new Homework));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = auth()->id();

        Homework::create($data);

        return redirect()->route('homework.homeworks.index')->with('status', 'Homework created.');
    }

    public function edit(Homework $homework)
    {
        return view('homework::homeworks.form', $this->formData($homework));
    }

    public function update(Request $request, Homework $homework)
    {
        $homework->update($this->validated($request));

        return redirect()->route('homework.homeworks.index')->with('status', 'Homework updated.');
    }

    public function destroy(Homework $homework)
    {
        $homework->delete();

        return redirect()->route('homework.homeworks.index')->with('status', 'Homework deleted.');
    }

    private function formData(Homework $homework): array
    {
        return [
            'homework' => $homework,
            'classes'  => SchoolClass::active()->orderBy('name')->get(),
            'sections' => Section::active()->orderBy('name')->get(),
            'subjects' => Subject::active()->orderBy('name')->get(),
            'teachers' => Staff::teachers()->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'class_id'         => ['required', 'exists:classes,id'],
            'section_id'       => ['required', 'exists:sections,id'],
            'subject_id'       => ['required', 'exists:subjects,id'],
            'teacher_id'       => ['nullable', 'exists:staff,id'],
            'title'            => ['required', 'string', 'max:200'],
            'description'      => ['nullable', 'string'],
            'homework_date'    => ['required', 'date'],
            'submission_date'  => ['required', 'date', 'after_or_equal:homework_date'],
            'evaluation_marks' => ['nullable', 'numeric', 'min:0'],
        ]);
    }
}
