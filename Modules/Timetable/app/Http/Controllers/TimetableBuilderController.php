<?php

namespace Modules\Timetable\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Subject;
use Modules\HumanResource\Models\Staff;
use Modules\Timetable\Models\Classroom;
use Modules\Timetable\Services\TimetableService;

class TimetableBuilderController extends Controller
{
    public function edit(Request $request, TimetableService $timetable)
    {
        $classId = $request->integer('class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;

        $grid = ($classId && $sectionId) ? $timetable->classGrid($classId, $sectionId) : null;
        $clashes = ($classId && $sectionId) ? $timetable->clashesFor($classId, $sectionId) : [];

        return view('timetable::builder', compact('classId', 'sectionId', 'grid', 'clashes') + [
            'classes'  => SchoolClass::active()->orderBy('name')->get(),
            'sections' => Section::active()->orderBy('name')->get(),
            'subjects' => Subject::active()->orderBy('name')->get(),
            'teachers' => Staff::where('is_active', true)->orderBy('full_name')->get(),
            'rooms'    => Classroom::active()->orderBy('room_no')->get(),
        ]);
    }

    public function update(Request $request, TimetableService $timetable)
    {
        $request->validate([
            'class_id'   => ['required', 'exists:classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'entries'    => ['array'],
        ]);

        $entries = $request->input('entries', []);

        foreach ($entries as $periodId => $days) {
            foreach ($days as $day => $cell) {
                $timetable->setEntry(
                    (int) $request->class_id,
                    (int) $request->section_id,
                    $day,
                    (int) $periodId,
                    [
                        'subject_id'   => $cell['subject_id'] ?? null,
                        'teacher_id'   => $cell['teacher_id'] ?? null,
                        'classroom_id' => $cell['classroom_id'] ?? null,
                    ]
                );
            }
        }

        return redirect()
            ->route('timetable.builder', ['class_id' => $request->class_id, 'section_id' => $request->section_id])
            ->with('status', 'Timetable saved.');
    }
}
