<?php

namespace Modules\Timetable\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\HumanResource\Models\Staff;
use Modules\Timetable\Services\TimetableService;

class RoutineController extends Controller
{
    public function classRoutine(Request $request, TimetableService $timetable)
    {
        $classId = $request->integer('class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;

        $grid = ($classId && $sectionId) ? $timetable->classGrid($classId, $sectionId) : null;

        return view('timetable::routine', compact('classId', 'sectionId', 'grid') + [
            'classes'  => SchoolClass::active()->orderBy('name')->get(),
            'sections' => Section::active()->orderBy('name')->get(),
        ]);
    }

    public function teacherRoutine(Request $request, TimetableService $timetable)
    {
        $teacherId = $request->integer('teacher_id') ?: null;

        $grid = $teacherId ? $timetable->teacherGrid($teacherId) : null;

        return view('timetable::teacher', compact('teacherId', 'grid') + [
            'teachers' => Staff::where('is_active', true)->orderBy('full_name')->get(),
        ]);
    }
}
