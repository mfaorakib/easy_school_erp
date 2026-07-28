<?php

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\Reports\Services\ReportService;

class StudentReportController extends Controller
{
    public function students(Request $request, ReportService $service)
    {
        $classId = $request->integer('class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;

        $rows = $service->studentList($classId, $sectionId);

        return view('reports::students', compact('rows', 'classId', 'sectionId') + [
            'classes'  => SchoolClass::active()->orderBy('name')->get(),
            'sections' => Section::active()->orderBy('name')->get(),
        ]);
    }

    public function guardians(Request $request, ReportService $service)
    {
        $classId = $request->integer('class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;

        $rows = $service->guardianList($classId, $sectionId);

        return view('reports::guardians', compact('rows', 'classId', 'sectionId') + [
            'classes'  => SchoolClass::active()->orderBy('name')->get(),
            'sections' => Section::active()->orderBy('name')->get(),
        ]);
    }
}
