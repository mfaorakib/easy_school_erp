<?php

namespace Modules\AcademicCore\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\ClassTeacherAssignment;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Services\ClassTeacherService;
use Modules\HumanResource\Models\Staff;

class ClassTeacherController extends Controller
{
    public function __construct(private readonly ClassTeacherService $service) {}

    public function index()
    {
        return view('academiccore::class_teachers.index', [
            'classes'     => SchoolClass::active()->orderBy('name')->get(),
            'sections'    => Section::active()->orderBy('name')->get(),
            'teachers'    => Staff::teachers()->get(),
            'assignments' => ClassTeacherAssignment::with(['schoolClass', 'section', 'teachers'])->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'class_id'    => ['required', 'integer', 'exists:classes,id'],
            'section_id'  => ['required', 'integer', 'exists:sections,id'],
            'teachers'    => ['required', 'array', 'min:1'],
            'teachers.*'  => ['integer', 'exists:staff,id'],
        ]);

        try {
            $this->service->assign($data['class_id'], $data['section_id'], $data['teachers']);
        } catch (\RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()->route('academic.class-teachers.index')->with('status', 'Class teacher assigned.');
    }

    public function destroy(ClassTeacherAssignment $classTeacher)
    {
        $this->service->delete($classTeacher);

        return redirect()->route('academic.class-teachers.index')->with('status', 'Assignment removed.');
    }
}
