<?php

namespace Modules\AcademicCore\Http\Controllers;

use App\Core\Support\Context;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Student;
use Modules\AcademicCore\Models\StudentRecord;
use Modules\AcademicCore\Services\PromotionService;
use Modules\Foundation\Models\AcademicYear;

class PromotionController extends Controller
{
    public function __construct(private readonly PromotionService $service) {}

    public function create(Request $request)
    {
        $fromClass   = $request->integer('from_class') ?: null;
        $fromSection = $request->integer('from_section') ?: null;

        $students = collect();
        if ($fromClass && $fromSection) {
            // Live enrollments in the active year for this class/section.
            $records = StudentRecord::live()
                ->where('class_id', $fromClass)
                ->where('section_id', $fromSection)
                ->with('student')
                ->get();
            $students = $records->map(fn ($r) => $r->student)->filter()->values();
        }

        return view('academiccore::promotion.create', [
            'classes'     => SchoolClass::active()->orderBy('name')->get(),
            'sections'    => Section::active()->orderBy('name')->get(),
            'years'       => AcademicYear::orderByDesc('id')->get(),
            'fromClass'   => $fromClass,
            'fromSection' => $fromSection,
            'students'    => $students,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'to_year_id'    => ['required', 'integer', 'exists:academic_years,id'],
            'to_class_id'   => ['required', 'integer', 'exists:classes,id'],
            'to_section_id' => ['required', 'integer', 'exists:sections,id'],
            'student_ids'   => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ]);

        $items = array_map(fn ($id) => [
            'student_id' => (int) $id,
            'class_id'   => (int) $data['to_class_id'],
            'section_id' => (int) $data['to_section_id'],
            'result'     => 'P',
        ], $data['student_ids']);

        $result = $this->service->promote(
            Context::academicYearId(),
            (int) $data['to_year_id'],
            $items,
        );

        return redirect()->route('academic.students.index')->with(
            'status',
            "Promotion done — promoted: {$result['promoted']}, skipped: {$result['skipped']}."
        );
    }
}
