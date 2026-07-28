<?php

namespace Modules\AcademicCore\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Subject;
use Modules\AcademicCore\Models\SubjectAssignment;
use Modules\AcademicCore\Services\SubjectAssignmentService;
use Modules\HumanResource\Models\Staff;

class SubjectAssignmentController extends Controller
{
    public function __construct(private readonly SubjectAssignmentService $service) {}

    public function index(Request $request)
    {
        $classId   = $request->integer('class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;

        $existing = collect();
        if ($classId) {
            $existing = SubjectAssignment::with(['subject', 'teacher', 'section'])
                ->where('class_id', $classId)
                ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
                ->get();
        }

        return view('academiccore::subject_assignments.index', [
            'classes'   => SchoolClass::active()->orderBy('name')->get(),
            'sections'  => Section::active()->orderBy('name')->get(),
            'subjects'  => Subject::active()->orderBy('name')->get(),
            'teachers'  => Staff::teachers()->get(),
            'classId'   => $classId,
            'sectionId' => $sectionId,
            'existing'  => $existing,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'class_id'    => ['required', 'integer', 'exists:classes,id'],
            'section_id'  => ['nullable', 'integer', 'exists:sections,id'],
            'subjects'    => ['required', 'array', 'min:1'],
            'subjects.*'  => ['integer', 'exists:subjects,id'],
            'teachers'    => ['array'],
        ]);

        $teachers = $request->input('teachers', []); // subject_id => teacher_id
        $pairs = array_map(fn ($subjectId) => [
            'subject_id' => (int) $subjectId,
            'teacher_id' => ! empty($teachers[$subjectId]) ? (int) $teachers[$subjectId] : null,
        ], $data['subjects']);

        $this->service->assign($data['class_id'], $data['section_id'] ?? null, $pairs, replace: true);

        return redirect()
            ->route('academic.subject-assignments.index', ['class_id' => $data['class_id'], 'section_id' => $data['section_id'] ?? null])
            ->with('status', 'Subjects assigned.');
    }
}
