<?php

namespace Modules\AcademicCore\Services;

use App\Core\Support\Context;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\AcademicCore\Models\Guardian;
use Modules\AcademicCore\Models\Student;
use Modules\Access\Enums\Role;
use Modules\Foundation\Services\SettingService;
use Modules\Foundation\Support\IdPattern;

/**
 * Student admission (the reference system SmStudentAdmissionController::store).
 *  - creates a role=student user (default password 123456)
 *  - resolves/creates the guardian (+ role=parent user)
 *  - auto admission number (max + 1) when not supplied
 *  - a formatted `unique_id` (admin-configurable pattern — see generateUniqueId())
 *  - enrols the student for the active year via EnrollmentService (is_default=1)
 *
 * Shared by both entry points: the admin "walk-in" form (StudentController) and
 * Modules\Admission's confirm flow (a pending public application becoming a
 * real student) — so every student gets the same id scheme regardless of route.
 */
class StudentAdmissionService
{
    private const DEFAULT_PASSWORD = '123456';

    private const DEFAULT_ID_FORMAT = 'STU-{YYYY}-{SEQ:4}';

    public function __construct(
        private readonly EnrollmentService $enrollment,
        private readonly SettingService $settings,
    ) {}

    public function admit(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            $yearId   = Context::academicYearId();
            $fullName = trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? ''));

            $guardian = $this->resolveGuardian($data);

            $admissionNo = $data['admission_no']
                ?? ((int) Student::max('admission_no') + 1);

            $uniqueId = $data['unique_id'] ?? $this->generateUniqueId();

            $user = User::create([
                'name'      => $fullName,
                'username'  => $data['mobile'] ?? $data['email'] ?? (string) $admissionNo,
                'email'     => $data['email'] ?? null,
                'phone'     => $data['mobile'] ?? null,
                'password'  => Hash::make(self::DEFAULT_PASSWORD),
                'is_active' => true,
            ]);
            $user->assignRole(Role::Student->value);

            $student = Student::create([
                'user_id'             => $user->id,
                'guardian_id'         => $guardian?->id,
                'admission_no'        => $admissionNo,
                'unique_id'           => $uniqueId,
                'admission_date'      => $data['admission_date'] ?? now()->toDateString(),
                'first_name'          => $data['first_name'],
                'last_name'           => $data['last_name'] ?? null,
                'full_name'           => $fullName,
                'gender_id'           => $data['gender_id'] ?? null,
                'religion_id'         => $data['religion_id'] ?? null,
                'blood_group_id'      => $data['blood_group_id'] ?? null,
                'date_of_birth'       => $data['date_of_birth'] ?? null,
                'email'               => $data['email'] ?? null,
                'mobile'              => $data['mobile'] ?? null,
                'photo'               => $data['photo'] ?? null,
                'student_category_id' => $data['student_category_id'] ?? null,
                'student_group_id'    => $data['student_group_id'] ?? null,
                'admission_year_id'   => $yearId,
                'is_active'           => true,
            ]);

            $this->enrollment->enroll(
                studentId: $student->id,
                classId:   $data['class_id'],
                sectionId: $data['section_id'],
                yearId:    $yearId,
                rollNo:    $data['roll_no'] ?? null,
                isDefault: true,
                isPromote: false,
            );

            return $student;
        });
    }

    /**
     * The next formatted unique student ID, per the admin-configured pattern
     * (Foundation setting `admission.id_format`, e.g. "STU-{YYYY}-{SEQ:4}").
     * The sequence is simply "how many students already have one" + 1 — same
     * max+1 spirit as admission_no, just formatted.
     */
    public function generateUniqueId(): string
    {
        $pattern = $this->settings->get('admission.id_format', self::DEFAULT_ID_FORMAT);
        $seq     = Student::whereNotNull('unique_id')->count() + 1;

        return IdPattern::format($pattern, (int) date('Y'), $seq);
    }

    /** Reuse existing guardian by id, else create one (+ parent user) when info present. */
    private function resolveGuardian(array $data): ?Guardian
    {
        if (! empty($data['guardian_id'])) {
            return Guardian::find($data['guardian_id']);
        }

        $name   = $data['guardian_name'] ?? null;
        $mobile = $data['guardian_mobile'] ?? null;
        $email  = $data['guardian_email'] ?? null;

        if (! $name && ! $mobile && ! $email) {
            return null;
        }

        $user = User::create([
            'name'      => $name ?? 'Guardian',
            'username'  => $mobile ?? $email,
            'email'     => $email,
            'phone'     => $mobile,
            'password'  => Hash::make(self::DEFAULT_PASSWORD),
            'is_active' => true,
        ]);
        $user->assignRole(Role::Parents->value);

        return Guardian::create([
            'user_id'           => $user->id,
            'guardian_name'     => $name,
            'guardian_mobile'   => $mobile,
            'guardian_email'    => $email,
            'guardian_relation' => $data['guardian_relation'] ?? null,
            'father_name'       => $data['father_name'] ?? null,
            'mother_name'       => $data['mother_name'] ?? null,
            'is_active'         => true,
        ]);
    }
}
