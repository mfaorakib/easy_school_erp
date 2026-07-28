<?php

namespace Modules\Fees\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Student;
use Modules\Fees\Models\StudentFine;
use Modules\Fees\Services\StudentFineService;

/**
 * Ad-hoc, one-off student fines (disciplinary/other reasons) — separate from
 * the structured Fee Group/Type/Master/Assignment collection flow.
 */
class StudentFineController extends Controller
{
    public function __construct(private readonly StudentFineService $service) {}

    public function index(Request $request)
    {
        $classId = $request->integer('class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;
        $status = $request->input('status') ?: null;

        return view('fees::fines.index', [
            'fines'     => $this->service->filterStudentFines($classId, $sectionId, $status),
            'students'  => $this->service->studentsForFilter($classId, $sectionId),
            'classes'   => SchoolClass::active()->orderBy('name')->get(),
            'sections'  => Section::active()->orderBy('name')->get(),
            'classId'   => $classId,
            'sectionId' => $sectionId,
            'status'    => $status,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'reason'     => ['required', 'string', 'max:191'],
            'amount'     => ['required', 'numeric', 'min:0.01'],
            'fine_date'  => ['nullable', 'date'],
        ]);

        $this->service->impose(
            Student::findOrFail($data['student_id']),
            $data['reason'],
            (float) $data['amount'],
            $data['fine_date'] ?? null,
        );

        return redirect()->route('fees.fines.index', $request->only(['class_id', 'section_id', 'status']))
            ->with('status', 'Fine recorded.');
    }

    public function collect(StudentFine $fine)
    {
        try {
            $this->service->collect($fine);
        } catch (\RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        return back()->with('status', 'Fine collected.');
    }

    public function receipt(StudentFine $fine)
    {
        abort_unless($fine->is_collected, 404);

        return view('fees::fines.receipt', [
            'fine' => $fine->load(['student.records.schoolClass', 'student.records.section', 'imposedBy']),
        ]);
    }
}
