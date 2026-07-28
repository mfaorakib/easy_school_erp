<?php

namespace Modules\HumanResource\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Access\Enums\Role;
use Modules\Documents\Models\CertificateTemplate;
use Modules\Documents\Services\DocumentService;
use Modules\HumanResource\Models\Staff;

/**
 * Staff lifecycle. Each staff member owns a user account carrying a single
 * non-student role (teacher, accountant, librarian, ...). Default password is
 * "password" for demo/dev; production should force a reset.
 */
class StaffService
{
    private const DEFAULT_PASSWORD = 'password';

    public function __construct(private readonly DocumentService $documents) {}

    public function create(array $data): Staff
    {
        return DB::transaction(function () use ($data) {
            $fullName = trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? ''));

            $user = User::create([
                'name'      => $fullName,
                'username'  => $data['mobile'] ?? $data['email'] ?? null,
                'email'     => $data['email'] ?? null,
                'phone'     => $data['mobile'] ?? null,
                'password'  => Hash::make(self::DEFAULT_PASSWORD),
                'is_active' => true,
            ]);
            $user->syncRoles([$this->role($data)]);

            $staff = Staff::create($this->attributes($data, $fullName, $user->id));

            // Auto-issue a numbered Appointment Letter the moment a staff
            // member is created (mirrors the lazy TC/EXP "Generate" flow,
            // just triggered eagerly here instead of on admin click).
            $appointmentTemplate = CertificateTemplate::where('name', 'Appointment Letter')->first();
            if ($appointmentTemplate) {
                $this->documents->documentNumber($appointmentTemplate, $staff);
            }

            return $staff;
        });
    }

    public function update(Staff $staff, array $data): Staff
    {
        return DB::transaction(function () use ($staff, $data) {
            $fullName = trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? ''));

            $staff->update($this->attributes($data, $fullName, $staff->user_id));

            if ($staff->user) {
                $staff->user->update([
                    'name'  => $fullName,
                    'email' => $data['email'] ?? $staff->user->email,
                    'phone' => $data['mobile'] ?? $staff->user->phone,
                ]);
                $staff->user->syncRoles([$this->role($data)]);
            }

            return $staff;
        });
    }

    public function delete(Staff $staff): void
    {
        DB::transaction(function () use ($staff) {
            $staff->user?->delete();
            $staff->delete();
        });
    }

    private function attributes(array $data, string $fullName, ?int $userId): array
    {
        return [
            'user_id'         => $userId,
            'staff_no'        => $data['staff_no'] ?? null,
            'first_name'      => $data['first_name'] ?? null,
            'last_name'       => $data['last_name'] ?? null,
            'full_name'       => $fullName,
            'designation_id'  => $data['designation_id'] ?? null,
            'department_id'   => $data['department_id'] ?? null,
            'gender_id'       => $data['gender_id'] ?? null,
            'date_of_birth'   => $data['date_of_birth'] ?? null,
            'date_of_joining' => $data['date_of_joining'] ?? null,
            'mobile'          => $data['mobile'] ?? null,
            'email'           => $data['email'] ?? null,
            'qualification'   => $data['qualification'] ?? null,
            'current_address' => $data['current_address'] ?? null,
            'basic_salary'    => $data['basic_salary'] ?? null,
            'is_active'       => $data['is_active'] ?? true,
        ];
    }

    private function role(array $data): string
    {
        $role = $data['role'] ?? Role::Teacher->value;

        // Never let a staff record be created as a student/parent.
        return in_array($role, [Role::Student->value, Role::Parents->value], true)
            ? Role::Teacher->value
            : $role;
    }
}
