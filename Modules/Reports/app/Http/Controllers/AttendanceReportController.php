<?php

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\Reports\Services\ReportService;

class AttendanceReportController extends Controller
{
    public function index(Request $request, ReportService $service)
    {
        $classId = $request->integer('class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $summary = ($classId && $sectionId)
            ? $service->attendanceSummary($classId, $sectionId, $from, $to)
            : null;

        return view('reports::attendance', compact('summary', 'classId', 'sectionId', 'from', 'to') + [
            'classes'  => SchoolClass::active()->orderBy('name')->get(),
            'sections' => Section::active()->orderBy('name')->get(),
        ]);
    }
}
