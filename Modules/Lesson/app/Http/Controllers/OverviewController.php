<?php

namespace Modules\Lesson\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\Lesson\Models\Lesson;

class OverviewController extends Controller
{
    public function index(Request $request)
    {
        $classId   = $request->integer('class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;

        $lessons = Lesson::with(['schoolClass', 'section', 'subject', 'teacher'])
            ->withCount('topics')
            ->withCount(['topics as completed_count' => fn ($q) => $q->where('is_complete', true)])
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->latest()
            ->get();

        return view('lesson::overview.index', [
            'classes'   => SchoolClass::active()->orderBy('name')->get(),
            'sections'  => Section::active()->orderBy('name')->get(),
            'classId'   => $classId,
            'sectionId' => $sectionId,
            'lessons'   => $lessons,
        ]);
    }
}
