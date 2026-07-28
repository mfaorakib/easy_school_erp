<?php

namespace Modules\Examination\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\Examination\Models\Exam;
use Modules\Examination\Models\ExamResult;
use Modules\Examination\Services\ExamResultService;

class ResultController extends Controller
{
    public function __construct(private readonly ExamResultService $service) {}

    public function index(Request $request)
    {
        $examId    = $request->integer('exam_id') ?: null;
        $classId   = $request->integer('class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;

        $results = collect();
        if ($examId && $classId && $sectionId) {
            $results = ExamResult::where('exam_id', $examId)->where('class_id', $classId)
                ->where('section_id', $sectionId)->with('student')->orderBy('position')->get();
        }

        return view('examination::results.index', [
            'exams'     => Exam::active()->orderBy('name')->get(),
            'classes'   => SchoolClass::active()->orderBy('name')->get(),
            'sections'  => Section::active()->orderBy('name')->get(),
            'examId'    => $examId,
            'classId'   => $classId,
            'sectionId' => $sectionId,
            'results'   => $results,
        ]);
    }

    public function compute(Request $request)
    {
        $data = $request->validate([
            'exam_id'    => ['required', 'integer', 'exists:exams,id'],
            'class_id'   => ['required', 'integer', 'exists:classes,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
        ]);

        $n = $this->service->computeResults($data['exam_id'], $data['class_id'], $data['section_id']);

        return redirect()->route('exam.results.index', $data)
            ->with('status', "Results computed for {$n} students.");
    }
}
