<?php

namespace Modules\Lesson\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Subject;
use Modules\HumanResource\Models\Staff;
use Modules\Lesson\Models\Lesson;

class LessonController extends Controller
{
    public function index()
    {
        $lessons = Lesson::with(['schoolClass', 'section', 'subject'])->latest()->get();

        return view('lesson::lessons.index', compact('lessons'));
    }

    public function create()
    {
        return view('lesson::lessons.form', $this->formData(new Lesson));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = auth()->id();

        Lesson::create($data);

        return redirect()->route('lesson.lessons.index')->with('status', 'Lesson created.');
    }

    public function edit(Lesson $lesson)
    {
        return view('lesson::lessons.form', $this->formData($lesson));
    }

    public function update(Request $request, Lesson $lesson)
    {
        $lesson->update($this->validated($request));

        return redirect()->route('lesson.lessons.index')->with('status', 'Lesson updated.');
    }

    public function destroy(Lesson $lesson)
    {
        $lesson->delete();

        return redirect()->route('lesson.lessons.index')->with('status', 'Lesson deleted.');
    }

    private function formData(Lesson $lesson): array
    {
        return [
            'lesson'   => $lesson,
            'classes'  => SchoolClass::active()->orderBy('name')->get(),
            'sections' => Section::active()->orderBy('name')->get(),
            'subjects' => Subject::active()->orderBy('name')->get(),
            'teachers' => Staff::teachers()->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'class_id'    => ['required', 'exists:classes,id'],
            'section_id'  => ['required', 'exists:sections,id'],
            'subject_id'  => ['required', 'exists:subjects,id'],
            'teacher_id'  => ['nullable', 'exists:staff,id'],
            'title'       => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'lesson_date' => ['nullable', 'date'],
        ]);
    }
}
