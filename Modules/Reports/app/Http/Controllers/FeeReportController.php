<?php

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\Reports\Services\ReportService;

class FeeReportController extends Controller
{
    public function collection(Request $request, ReportService $service)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());
        $classId = $request->integer('class_id') ?: null;

        $data = $service->feesCollection($from, $to, $classId);

        $classes = SchoolClass::active()->orderBy('name')->get();

        return view('reports::fees_collection', compact('data', 'from', 'to', 'classId', 'classes'));
    }

    public function due(Request $request, ReportService $service)
    {
        $classId = $request->integer('class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;

        $data = $service->feesDue($classId, $sectionId);

        $classes = SchoolClass::active()->orderBy('name')->get();
        $sections = Section::active()->orderBy('name')->get();

        return view('reports::fees_due', compact('data', 'classId', 'sectionId', 'classes', 'sections'));
    }
}
