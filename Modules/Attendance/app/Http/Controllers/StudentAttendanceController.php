<?php

namespace Modules\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Services\AttendanceService;

class StudentAttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $service) {}

    public function index(Request $request)
    {
        $classId   = $request->integer('class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;
        $date      = $request->date('date')?->toDateString() ?? now()->toDateString();

        $roster = collect();
        if ($classId && $sectionId) {
            $roster = $this->service->studentRoster($classId, $sectionId, $date);
        }

        return view('attendance::student.index', [
            'classes'   => SchoolClass::active()->orderBy('name')->get(),
            'sections'  => Section::active()->orderBy('name')->get(),
            'statuses'  => AttendanceStatus::markable(),
            'classId'   => $classId,
            'sectionId' => $sectionId,
            'date'      => $date,
            'roster'    => $roster,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'class_id'   => ['required', 'integer', 'exists:classes,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'date'       => ['required', 'date'],
            'status'     => ['required', 'array', 'min:1'],
            'status.*'   => ['required', 'in:P,L,A,F'],
            'note'       => ['array'],
        ]);

        $count = $this->service->saveStudentAttendance(
            $data['class_id'], $data['section_id'], $data['date'],
            $data['status'], $request->input('note', []),
        );

        return redirect()
            ->route('attendance.student.index', ['class_id' => $data['class_id'], 'section_id' => $data['section_id'], 'date' => $data['date']])
            ->with('status', "Attendance saved for {$count} students.");
    }
}
