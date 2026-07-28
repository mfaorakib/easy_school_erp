<?php

namespace Modules\Examination\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Subject;
use Modules\Examination\Models\Exam;
use Modules\Examination\Models\ExamSchedule;

class ExamScheduleController extends Controller
{
    public function index(Request $request)
    {
        $examId    = $request->integer('exam_id') ?: null;
        $classId   = $request->integer('class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;

        $subjects  = collect();
        $existing  = collect();
        if ($examId && $classId && $sectionId) {
            $subjects = Subject::where('is_active', true)->orderBy('name')->get();
            $existing = ExamSchedule::where('exam_id', $examId)->where('class_id', $classId)
                ->where('section_id', $sectionId)->get()->keyBy('subject_id');
        }

        return view('examination::schedules.index', [
            'exams'     => Exam::active()->orderBy('name')->get(),
            'classes'   => SchoolClass::active()->orderBy('name')->get(),
            'sections'  => Section::active()->orderBy('name')->get(),
            'examId'    => $examId,
            'classId'   => $classId,
            'sectionId' => $sectionId,
            'subjects'  => $subjects,
            'existing'  => $existing,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'exam_id'    => ['required', 'integer', 'exists:exams,id'],
            'class_id'   => ['required', 'integer', 'exists:classes,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'include'    => ['array'],
            'full_mark'  => ['array'],
            'pass_mark'  => ['array'],
            'exam_date'  => ['array'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $subjects = Subject::where('is_active', true)->pluck('id');
            foreach ($subjects as $sid) {
                $included = ! empty($data['include'][$sid]);
                $query = ExamSchedule::where('exam_id', $data['exam_id'])->where('class_id', $data['class_id'])
                    ->where('section_id', $data['section_id'])->where('subject_id', $sid);

                if (! $included) {
                    $query->delete();
                    continue;
                }

                ExamSchedule::updateOrCreate(
                    ['exam_id' => $data['exam_id'], 'class_id' => $data['class_id'], 'section_id' => $data['section_id'], 'subject_id' => $sid],
                    [
                        'exam_date' => $request->input("exam_date.$sid") ?: null,
                        'full_mark' => $request->input("full_mark.$sid", 100),
                        'pass_mark' => $request->input("pass_mark.$sid", 33),
                    ],
                );
            }
        });

        return redirect()->route('exam.schedules.index', $request->only('exam_id', 'class_id', 'section_id'))
            ->with('status', 'Schedule saved.');
    }
}
