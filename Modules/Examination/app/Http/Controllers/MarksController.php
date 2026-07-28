<?php

namespace Modules\Examination\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\Examination\Models\Exam;
use Modules\Examination\Models\ExamSchedule;
use Modules\Examination\Services\ExamResultService;

class MarksController extends Controller
{
    public function __construct(private readonly ExamResultService $service) {}

    public function index(Request $request)
    {
        $examId    = $request->integer('exam_id') ?: null;
        $classId   = $request->integer('class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;
        $subjectId = $request->integer('subject_id') ?: null;

        $roster   = [];
        $schedule = null;
        $subjects = collect();
        if ($examId && $classId && $sectionId) {
            $subjects = ExamSchedule::where('exam_id', $examId)->where('class_id', $classId)
                ->where('section_id', $sectionId)->with('subject')->get();
            if ($subjectId) {
                $schedule = $subjects->firstWhere('subject_id', $subjectId);
                $roster   = $this->service->marksRoster($examId, $classId, $sectionId, $subjectId);
            }
        }

        return view('examination::marks.index', [
            'exams'     => Exam::active()->orderBy('name')->get(),
            'classes'   => SchoolClass::active()->orderBy('name')->get(),
            'sections'  => Section::active()->orderBy('name')->get(),
            'subjects'  => $subjects,
            'examId'    => $examId,
            'classId'   => $classId,
            'sectionId' => $sectionId,
            'subjectId' => $subjectId,
            'schedule'  => $schedule,
            'roster'    => $roster,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'exam_id'    => ['required', 'integer', 'exists:exams,id'],
            'class_id'   => ['required', 'integer', 'exists:classes,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'marks'      => ['array'],
            'marks.*'    => ['nullable', 'numeric'],
            'absent'     => ['array'],
        ]);

        // The subject's full mark bounds every entered mark. Because the max is
        // dynamic (per schedule) it can't be expressed as a static wildcard rule,
        // so validate each entered mark explicitly against this schedule.
        $schedule = ExamSchedule::where('exam_id', $data['exam_id'])
            ->where('class_id', $data['class_id'])
            ->where('section_id', $data['section_id'])
            ->where('subject_id', $data['subject_id'])
            ->first();

        if (! $schedule) {
            return back()->withInput()->withErrors([
                'subject_id' => 'No exam schedule found for the selected subject.',
            ]);
        }

        $fullMark = (float) $schedule->full_mark;
        $absent   = (array) $request->input('absent', []);
        $errors   = [];
        foreach ((array) $request->input('marks', []) as $studentId => $mark) {
            // Absent students, or blank cells, legitimately have no mark.
            if (! empty($absent[$studentId] ?? null) || $mark === null || $mark === '') {
                continue;
            }
            if (! is_numeric($mark) || (float) $mark < 0 || (float) $mark > $fullMark) {
                $errors["marks.$studentId"] = "Each mark must be a number between 0 and {$fullMark}.";
            }
        }

        if ($errors) {
            return back()->withInput()->withErrors($errors);
        }

        $rows = [];
        foreach ($request->input('marks', []) as $studentId => $mark) {
            $rows[$studentId] = ['marks' => $mark, 'absent' => ! empty($request->input("absent.$studentId"))];
        }

        $this->service->saveMarks($data['exam_id'], $data['class_id'], $data['section_id'], $data['subject_id'], $rows);

        return redirect()->route('exam.marks.index', $request->only('exam_id', 'class_id', 'section_id', 'subject_id'))
            ->with('status', 'Marks saved.');
    }
}
