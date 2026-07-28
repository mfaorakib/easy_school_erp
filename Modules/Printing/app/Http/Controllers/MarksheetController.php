<?php

namespace Modules\Printing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\StudentRecord;
use Modules\Examination\Models\Exam;
use Modules\Printing\Services\PrintService;

class MarksheetController extends Controller
{
    public function index(Request $request)
    {
        $examId = $request->integer('exam_id') ?: null;
        $classId = $request->integer('class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;

        $students = ($classId && $sectionId)
            ? StudentRecord::live()
                ->where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->with('student')
                ->orderBy('roll_no')
                ->get()
                ->pluck('student')
                ->filter()
                ->values()
            : collect();

        return view('printing::marksheet', compact('examId', 'classId', 'sectionId', 'students') + [
            'exams'    => Exam::with('type')->latest()->get(),
            'classes'  => SchoolClass::active()->orderBy('name')->get(),
            'sections' => Section::active()->orderBy('name')->get(),
        ]);
    }

    public function generate(Request $request, PrintService $print)
    {
        $request->validate([
            'exam_id'      => ['required', 'exists:exams,id'],
            'holder_ids'   => ['required', 'array', 'min:1'],
            'holder_ids.*' => ['integer'],
        ]);

        $examId = (int) $request->exam_id;

        $sheets = collect($request->input('holder_ids', []))
            ->map(fn ($sid) => $print->marksheet($examId, (int) $sid))
            ->all();

        return view('printing::print.marksheet', compact('sheets'));
    }
}
