<?php

namespace Modules\Printing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\Examination\Models\Exam;
use Modules\Printing\Services\PrintService;

class TabulationController extends Controller
{
    public function index()
    {
        return view('printing::tabulation', [
            'exams'    => Exam::with('type')->latest()->get(),
            'classes'  => SchoolClass::active()->orderBy('name')->get(),
            'sections' => Section::active()->orderBy('name')->get(),
        ]);
    }

    public function generate(Request $request, PrintService $print)
    {
        $request->validate([
            'exam_id'    => ['required', 'exists:exams,id'],
            'class_id'   => ['required', 'exists:classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
        ]);

        $data = $print->tabulation(
            (int) $request->exam_id,
            (int) $request->class_id,
            (int) $request->section_id
        );

        return view('printing::print.tabulation', compact('data'));
    }
}
