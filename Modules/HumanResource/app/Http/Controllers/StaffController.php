<?php

namespace Modules\HumanResource\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Access\Enums\Role;
use Modules\Attendance\Models\StaffAttendance;
use Modules\Foundation\Models\AcademicYear;
use Modules\Foundation\Models\BaseGroup;
use Modules\Foundation\Models\Department;
use Modules\Foundation\Models\Designation;
use Modules\HumanResource\Models\Staff;
use Modules\HumanResource\Services\StaffService;
use Modules\Leave\Models\LeaveApplication;
use Modules\Payroll\Models\Payslip;

class StaffController extends Controller
{
    /** Roles a staff member may hold (never student/parent). */
    private const STAFF_ROLES = [
        Role::Teacher, Role::Admin, Role::Accountant,
        Role::Receptionist, Role::Librarian, Role::Driver,
    ];

    public function __construct(private readonly StaffService $service) {}

    public function index()
    {
        $staff = Staff::with(['user', 'designation', 'department'])->orderByDesc('id')->paginate(20);

        return view('humanresource::staff.index', compact('staff'));
    }

    /**
     * The consolidated "everything about this staff member" profile —
     * employment info, attendance, payslip history, and leave history for a
     * CHOSEN academic year (defaults to the currently-active one). Like the
     * Student profile, the year picker never touches the system-wide active
     * year — everything here is queried explicitly for the chosen year, so
     * it can never affect what any other admin sees at the same time.
     *
     * staff_attendances has no academic_year_id column (unlike
     * student_attendances), so its year filter uses the AcademicYear's own
     * start_date/end_date range instead.
     */
    public function show(Request $request, Staff $staff)
    {
        $staff->load(['designation', 'department', 'user']);

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $yearId = $request->integer('year') ?: optional(AcademicYear::current())->id;
        $year = $academicYears->firstWhere('id', $yearId) ?? AcademicYear::current();

        $attendance = $year
            ? StaffAttendance::where('staff_id', $staff->id)
                ->whereBetween('date', [$year->start_date, $year->end_date])
                ->orderBy('date')
                ->get()
            : collect();
        $attendanceSummary = $attendance->groupBy(fn ($a) => $a->status instanceof \BackedEnum ? $a->status->value : $a->status)->map->count();

        $payslips = $year
            ? Payslip::allYears()
                ->where('staff_id', $staff->id)
                ->where('academic_year_id', $year->id)
                ->orderBy('period')
                ->get()
            : collect();

        $leaveApplications = $year
            ? LeaveApplication::allYears()
                ->where('staff_id', $staff->id)
                ->where('academic_year_id', $year->id)
                ->with('type')
                ->latest('from_date')
                ->get()
            : collect();

        return view('humanresource::staff.show', compact(
            'staff', 'academicYears', 'year',
            'attendance', 'attendanceSummary', 'payslips', 'leaveApplications',
        ));
    }

    public function create()
    {
        return view('humanresource::staff.form', $this->formData(new Staff));
    }

    public function store(Request $request)
    {
        $this->service->create($this->validated($request));

        return redirect()->route('hr.staff.index')->with('status', 'Staff member added.');
    }

    public function edit(Staff $staff)
    {
        return view('humanresource::staff.form', $this->formData($staff));
    }

    public function update(Request $request, Staff $staff)
    {
        $this->service->update($staff, $this->validated($request));

        return redirect()->route('hr.staff.index')->with('status', 'Staff member updated.');
    }

    public function destroy(Staff $staff)
    {
        $this->service->delete($staff);

        return redirect()->route('hr.staff.index')->with('status', 'Staff member removed.');
    }

    private function formData(Staff $staff): array
    {
        return [
            'staff'        => $staff,
            'designations' => Designation::where('is_active', true)->orderBy('title')->get(),
            'departments'  => Department::where('is_active', true)->orderBy('name')->get(),
            'genders'      => optional(BaseGroup::where('slug', 'gender')->first())?->setups()->get() ?? collect(),
            'roles'        => self::STAFF_ROLES,
            'currentRole'  => $staff->user?->getRoleNames()->first(),
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'first_name'      => ['required', 'string', 'max:100'],
            'last_name'       => ['nullable', 'string', 'max:100'],
            'role'            => ['required', 'string'],
            'designation_id'  => ['nullable', 'integer', 'exists:designations,id'],
            'department_id'   => ['nullable', 'integer', 'exists:departments,id'],
            'gender_id'       => ['nullable', 'integer', 'exists:base_setups,id'],
            'email'           => ['nullable', 'email', 'max:120'],
            'mobile'          => ['nullable', 'string', 'max:30'],
            'date_of_birth'   => ['nullable', 'date'],
            'date_of_joining' => ['nullable', 'date'],
            'leaving_date'    => ['nullable', 'date'],
            'qualification'   => ['nullable', 'string', 'max:200'],
            'current_address' => ['nullable', 'string', 'max:500'],
            'basic_salary'    => ['nullable', 'numeric', 'min:0'],
            'bank_name'       => ['nullable', 'string', 'max:150'],
            'bank_account_no' => ['nullable', 'string', 'max:60'],
            'bank_branch'     => ['nullable', 'string', 'max:150'],
            'is_active'       => ['sometimes', 'boolean'],
        ]);

        // A checkbox that is unchecked is simply absent from the request, so `sometimes`
        // above would leave `is_active` missing on an edit and StaffService::attributes()
        // would then default it back to true. Force it explicitly so unchecked -> false.
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
