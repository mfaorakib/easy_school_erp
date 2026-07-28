<?php

namespace Modules\AcademicCore\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Student;
use Modules\AcademicCore\Models\StudentCategory;
use Modules\AcademicCore\Models\StudentGroup;
use Modules\AcademicCore\Models\StudentRecord;
use Modules\AcademicCore\Services\StudentAdmissionService;
use Modules\Attendance\Models\StudentAttendance;
use Modules\Examination\Models\ExamResult;
use Modules\Fees\Services\FeeService;
use Modules\Foundation\Models\AcademicYear;
use Modules\Foundation\Models\BaseGroup;

class StudentController extends Controller
{
    public function __construct(private readonly StudentAdmissionService $admission) {}

    public function index()
    {
        $students = Student::with(['liveRecord.schoolClass', 'liveRecord.section', 'guardian'])
            ->where('is_active', true)
            ->orderByDesc('id')
            ->paginate(20);

        return view('academiccore::students.index', compact('students'));
    }

    /**
     * The consolidated "everything about this student" profile — admission
     * info, class/section/roll, attendance, fee ledger, and exam results for
     * a CHOSEN academic year (defaults to the currently-active one). The
     * year picker deliberately does NOT touch the system-wide active year
     * (Context::academicYearId()) — it queries explicitly via allYears() +
     * an academic_year_id filter instead, so browsing a past session here
     * can never affect what any other admin or page sees at the same time.
     */
    public function show(Request $request, Student $student)
    {
        $student->load(['guardian', 'gender']);

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $yearId = $request->integer('year') ?: optional(AcademicYear::current())->id;
        $year = $academicYears->firstWhere('id', $yearId) ?? AcademicYear::current();

        $record = $year
            ? StudentRecord::allYears()
                ->where('student_id', $student->id)
                ->where('academic_year_id', $year->id)
                ->with(['schoolClass', 'section'])
                ->first()
            : null;

        $attendance = $year
            ? StudentAttendance::where('student_id', $student->id)
                ->whereNull('subject_id')
                ->where('academic_year_id', $year->id)
                ->get()
            : collect();
        $attendanceSummary = $attendance->groupBy(fn ($a) => $a->status instanceof \BackedEnum ? $a->status->value : $a->status)->map->count();

        $feeAssignments = $year
            ? \Modules\Fees\Models\FeeAssignment::allYears()
                ->where('student_id', $student->id)
                ->where('academic_year_id', $year->id)
                ->with(['master.type', 'discounts', 'payments'])
                ->get()
                ->map(fn ($a) => ['assignment' => $a, 'balance' => app(FeeService::class)->balance($a)])
            : collect();

        $examResults = $year
            ? ExamResult::allYears()
                ->where('student_id', $student->id)
                ->where('academic_year_id', $year->id)
                ->with('exam')
                ->get()
            : collect();

        $documents = $student->documents()->with('type')->latest()->get();

        return view('academiccore::students.show', compact(
            'student', 'academicYears', 'year',
            'record', 'attendance', 'attendanceSummary',
            'feeAssignments', 'examResults', 'documents',
        ));
    }

    public function create()
    {
        return view('academiccore::students.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'      => ['required', 'string', 'max:70'],
            'last_name'       => ['nullable', 'string', 'max:70'],
            'gender_id'       => ['nullable', 'integer', 'exists:base_setups,id'],
            'date_of_birth'   => ['nullable', 'date'],
            'email'           => ['nullable', 'email', 'max:120'],
            'mobile'          => ['nullable', 'string', 'max:30'],
            'admission_no'    => ['nullable', 'integer'],
            'roll_no'         => ['nullable', 'integer'],
            'class_id'        => ['required', 'integer', 'exists:classes,id'],
            'section_id'      => ['required', 'integer', 'exists:sections,id'],
            'student_category_id' => ['nullable', 'integer', 'exists:student_categories,id'],
            'student_group_id'    => ['nullable', 'integer', 'exists:student_groups,id'],
            'guardian_name'   => ['nullable', 'string', 'max:120'],
            'guardian_mobile' => ['nullable', 'string', 'max:30'],
            'guardian_email'  => ['nullable', 'email', 'max:120'],
            'guardian_relation' => ['nullable', 'string', 'max:50'],
        ]);

        $student = $this->admission->admit($data);

        return redirect()->route('academic.students.index')
            ->with('status', "Student {$student->full_name} admitted (Admission #{$student->admission_no}).");
    }

    private function formData(): array
    {
        return [
            'classes'    => SchoolClass::active()->orderBy('name')->get(),
            'sections'   => Section::active()->orderBy('name')->get(),
            'categories' => StudentCategory::orderBy('name')->get(),
            'groups'     => StudentGroup::orderBy('name')->get(),
            'genders'    => optional(BaseGroup::where('slug', 'gender')->first())
                                ->setups()->where('is_active', true)->get() ?? collect(),
            'nextAdmission' => (int) Student::max('admission_no') + 1,
        ];
    }
}
