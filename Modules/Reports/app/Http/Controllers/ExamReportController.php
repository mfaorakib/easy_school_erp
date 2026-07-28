<?php

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\Examination\Models\Exam;
use Modules\Reports\Services\ReportService;

class ExamReportController extends Controller
{
    public function results(Request $request, ReportService $service)
    {
        $examId = $request->integer('exam_id') ?: null;
        $classId = $request->integer('class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;

        $results = ($examId && $classId && $sectionId)
            ? $service->examResults($examId, $classId, $sectionId)
            : null;

        return view('reports::exam_results', compact('results', 'examId', 'classId', 'sectionId') + [
            'classes'  => SchoolClass::active()->orderBy('name')->get(),
            'sections' => Section::active()->orderBy('name')->get(),
            'exams'    => Exam::with('type')->latest()->get(),
        ]);
    }

    public function merit(Request $request, ReportService $service)
    {
        $examId = $request->integer('exam_id') ?: null;
        $classId = $request->integer('class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;
        $limit = $request->integer('limit') ?: 10;

        $results = ($examId && $classId && $sectionId)
            ? $service->meritList($examId, $classId, $sectionId, $limit)
            : null;

        return view('reports::merit_list', compact('results', 'examId', 'classId', 'sectionId', 'limit') + [
            'classes'  => SchoolClass::active()->orderBy('name')->get(),
            'sections' => Section::active()->orderBy('name')->get(),
            'exams'    => Exam::with('type')->latest()->get(),
        ]);
    }
}
