<?php

namespace Modules\Access\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Access\Enums\Role as RoleEnum;
use Modules\Access\Support\PermissionRegistry;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeds every permission in PermissionRegistry, then gives each of the 9
 * fixed roles a sensible starting set — the new Role & Permission admin UI
 * is exactly where a school fine-tunes these afterwards.
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionRegistry::keys() as $key) {
            Permission::firstOrCreate(['name' => $key, 'guard_name' => 'web']);
        }

        $all = PermissionRegistry::keys();

        $defaults = [
            RoleEnum::SuperAdmin->value => $all,
            // Everything except managing roles/permissions themselves (reserved for super-admin).
            RoleEnum::Admin->value => array_values(array_diff($all, ['roles.create', 'roles.edit', 'roles.delete'])),
            RoleEnum::Teacher->value => [
                'dashboard.view', 'academic.view', 'students.view',
                'attendance.student.view', 'attendance.student.mark',
                'leave.view', 'leave.apply',
                'homework.view', 'homework.create', 'homework.edit', 'homework.evaluate',
                'lesson.view', 'lesson.create', 'lesson.edit',
                'download_center.view',
                'online_exam.view', 'online_exam.create', 'online_exam.edit',
                'examination.marks.enter', 'examination.results.view',
                'communication.view', 'chat.use',
                'behaviour.view', 'behaviour.create',
            ],
            RoleEnum::Accountant->value => [
                'dashboard.view', 'reports.view',
                'fees.view', 'fees.collect', 'fees.discounts.attach', 'fees.intents.confirm',
                'fees.masters.view', 'fees.masters.create', 'fees.masters.edit',
                'accounting.view', 'accounting.create', 'accounting.edit',
                'payroll.view', 'wallet.view',
            ],
            RoleEnum::Receptionist->value => [
                'dashboard.view',
                'front_office.view', 'front_office.create', 'front_office.edit',
                'communication.view', 'communication.notices',
                'documents.generate',
            ],
            RoleEnum::Librarian->value => [
                'dashboard.view', 'library.view', 'library.create', 'library.edit', 'library.issue',
            ],
            RoleEnum::Driver->value => [
                'dashboard.view', 'transport.view',
            ],
            RoleEnum::Student->value => ['dashboard.view'],
            RoleEnum::Parents->value => ['dashboard.view'],
        ];

        foreach ($defaults as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->syncPermissions($permissions);
            }
        }
    }
}
