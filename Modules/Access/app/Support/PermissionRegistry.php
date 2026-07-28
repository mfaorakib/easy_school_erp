<?php

namespace Modules\Access\Support;

/**
 * The permission vocabulary for the whole app — one entry per
 * `{area}.{action}` permission, grouped for the role-edit checkbox matrix.
 * Action-level (view/create/edit/delete + a few domain-specific actions per
 * area), not just one permission per module — this is the single source of
 * truth consumed by:
 *   - PermissionSeeder (creates the actual Spatie Permission rows)
 *   - RolePermissionController's matrix view (groups checkboxes by area)
 *
 * Scope note: this registry defines and lets admins ASSIGN permissions.
 * Actually ENFORCING them (route middleware + nav filtering across every
 * module) is a deliberately separate, later phase — see docs/business-logic.
 */
class PermissionRegistry
{
    /** @return array<string, array<string, string>> group label => [permission key => description] */
    public static function all(): array
    {
        return [
            'Roles & Users' => [
                'roles.view'    => 'View roles',
                'roles.create'  => 'Create custom roles',
                'roles.edit'    => 'Edit a role\'s permissions',
                'roles.delete'  => 'Delete custom roles',
                'users.view'    => 'View user accounts',
                'users.assign_role' => 'Assign roles to a user',
            ],
            'Dashboard & Reports' => [
                'dashboard.view' => 'View the admin dashboard',
                'reports.view'   => 'View reports',
            ],
            'Academic Setup' => [
                'academic.view'   => 'View classes, sections, subjects',
                'academic.create' => 'Create classes, sections, subjects',
                'academic.edit'   => 'Edit classes, sections, subjects',
                'academic.delete' => 'Delete classes, sections, subjects',
            ],
            'Students' => [
                'students.view'    => 'View student records',
                'students.create'  => 'Admit / create students (walk-in)',
                'students.edit'    => 'Edit student records',
                'students.delete'  => 'Deactivate/delete students',
                'students.promote' => 'Promote students between years',
            ],
            'Admission' => [
                'admission.view'     => 'View admission applications',
                'admission.confirm'  => 'Confirm a pending application',
                'admission.reject'   => 'Reject a pending application',
                'admission.settings' => 'Configure the unique ID format',
            ],
            'Staff (HR)' => [
                'hr.view'   => 'View staff records',
                'hr.create' => 'Create staff',
                'hr.edit'   => 'Edit staff, departments, designations',
                'hr.delete' => 'Delete staff',
            ],
            'Attendance' => [
                'attendance.student.view' => 'View student attendance',
                'attendance.student.mark' => 'Mark student attendance',
                'attendance.staff.view'   => 'View staff attendance',
                'attendance.staff.mark'   => 'Mark staff attendance',
            ],
            'Leave' => [
                'leave.view'     => 'View leave applications',
                'leave.apply'    => 'Apply for leave',
                'leave.approve'  => 'Approve/reject leave applications',
                'leave.types.manage'      => 'Manage leave types',
                'leave.shifts.manage'     => 'Manage shifts and staff assignment',
                'leave.evaluation.manage' => 'Score teacher evaluations',
            ],
            'Fee Collection' => [
                'fees.view'            => 'View fee ledgers/balances',
                'fees.collect'         => 'Collect a fee payment',
                'fees.discounts.attach' => 'Attach/remove discounts and scholarships',
                'fees.intents.confirm' => 'Confirm/reject online payment intents',
            ],
            'Fee Setup' => [
                'fees.masters.view'   => 'View fee groups/types/masters',
                'fees.masters.create' => 'Create fee groups/types/masters',
                'fees.masters.edit'   => 'Edit fee groups/types/masters',
                'fees.masters.delete' => 'Delete fee groups/types/masters',
            ],
            'Accounting' => [
                'accounting.view'   => 'View income/expense/bank accounts',
                'accounting.create' => 'Record transactions/transfers',
                'accounting.edit'   => 'Edit account heads/transactions',
                'accounting.delete' => 'Delete transactions',
            ],
            'Payroll' => [
                'payroll.view'     => 'View salary templates/payslips',
                'payroll.create'   => 'Create salary templates/assignment',
                'payroll.edit'     => 'Edit salary templates/assignment',
                'payroll.generate' => 'Generate a payroll run',
            ],
            'Examination Setup' => [
                'examination.view'   => 'View exams, schedules, grades',
                'examination.create' => 'Create exams, schedules, grades',
                'examination.edit'   => 'Edit exams, schedules, grades',
                'examination.delete' => 'Delete exams, schedules, grades',
            ],
            'Marks & Results' => [
                'examination.marks.enter'   => 'Enter/edit marks',
                'examination.results.publish' => 'Publish results',
                'examination.results.view'  => 'View results/merit lists',
            ],
            'Timetable' => [
                'timetable.view'   => 'View the class routine',
                'timetable.create' => 'Create routine entries',
                'timetable.edit'   => 'Edit the timetable/periods/rooms',
                'timetable.delete' => 'Delete routine entries',
            ],
            'Library' => [
                'library.view'   => 'View books and members',
                'library.create' => 'Add books/categories/members',
                'library.edit'   => 'Edit books/categories/members',
                'library.delete' => 'Delete books/categories/members',
                'library.issue'  => 'Issue/return books',
            ],
            'Inventory' => [
                'inventory.view'   => 'View items and stock',
                'inventory.create' => 'Add items/categories/suppliers',
                'inventory.edit'   => 'Edit items/categories/suppliers',
                'inventory.delete' => 'Delete items/categories/suppliers',
                'inventory.sell'   => 'Sell/issue items',
            ],
            'Transport' => [
                'transport.view'   => 'View routes and vehicles',
                'transport.create' => 'Add routes/vehicles',
                'transport.edit'   => 'Edit routes/vehicles/student assignment',
                'transport.delete' => 'Delete routes/vehicles',
            ],
            'Dormitory' => [
                'dormitory.view'   => 'View dormitories and room allocation',
                'dormitory.create' => 'Add dormitories/room types',
                'dormitory.edit'   => 'Edit dormitories/allocation',
                'dormitory.delete' => 'Delete dormitories/room types',
            ],
            'Homework' => [
                'homework.view'     => 'View homework',
                'homework.create'   => 'Assign homework',
                'homework.edit'     => 'Edit homework',
                'homework.delete'   => 'Delete homework',
                'homework.evaluate' => 'Evaluate submitted homework',
            ],
            'Lesson Plan' => [
                'lesson.view'   => 'View lessons and topics',
                'lesson.create' => 'Create lessons/topics',
                'lesson.edit'   => 'Edit lessons/topics',
                'lesson.delete' => 'Delete lessons/topics',
            ],
            'Download Center' => [
                'download_center.view'   => 'View shared content',
                'download_center.create' => 'Upload shared content',
                'download_center.edit'   => 'Edit shared content',
                'download_center.delete' => 'Delete shared content',
            ],
            'Online Exam' => [
                'online_exam.view'   => 'View question bank/online exams',
                'online_exam.create' => 'Create questions/online exams',
                'online_exam.edit'   => 'Edit questions/online exams',
                'online_exam.delete' => 'Delete questions/online exams',
                'online_exam.take'   => 'Take an assigned online exam',
            ],
            'Behaviour' => [
                'behaviour.view'   => 'View behaviour records',
                'behaviour.create' => 'Record a behaviour incident',
                'behaviour.edit'   => 'Edit behaviour records/types',
                'behaviour.delete' => 'Delete behaviour records',
            ],
            'Communication' => [
                'communication.view'    => 'View notices/events',
                'communication.notices' => 'Manage the notice board',
                'communication.events'  => 'Manage events',
            ],
            'Chat' => [
                'chat.use' => 'Use internal chat/messaging',
            ],
            'Front Office' => [
                'front_office.view'   => 'View enquiries, visitors, complaints, postal',
                'front_office.create' => 'Log enquiries/visitors/complaints/postal',
                'front_office.edit'   => 'Edit/follow-up enquiries and complaints',
                'front_office.delete' => 'Delete front-office records',
            ],
            'Documents' => [
                'documents.templates.view'   => 'View ID card/certificate templates',
                'documents.templates.create' => 'Create templates',
                'documents.templates.edit'   => 'Edit templates',
                'documents.templates.delete' => 'Delete templates',
                'documents.generate'         => 'Generate ID cards/certificates',
            ],
            'Printing' => [
                'printing.use' => 'Use the print center (marksheets, invoices, etc.)',
            ],
            'Wallet' => [
                'wallet.view'   => 'View wallets and transaction history',
                'wallet.create' => 'Open a wallet for a user',
                'wallet.manage' => 'Deposit/withdraw wallet funds',
            ],
            'Website Builder' => [
                'builder.pages.view'      => 'View public pages/sections',
                'builder.pages.create'    => 'Create pages/sections',
                'builder.pages.edit'      => 'Edit pages/sections/content',
                'builder.pages.delete'    => 'Delete pages/sections',
                'builder.settings.manage' => 'Manage site settings, menus, sliders',
                'builder.messages.view'   => 'View contact/newsletter submissions',
            ],
            'System Settings' => [
                'settings.view'   => 'View general/localization/email/SMS/appearance settings',
                'settings.manage' => 'Edit general/localization/email/SMS/appearance settings',
            ],
        ];
    }

    /** Flat list of every permission key, in registry order. */
    public static function keys(): array
    {
        $keys = [];
        foreach (static::all() as $group) {
            $keys = array_merge($keys, array_keys($group));
        }

        return $keys;
    }
}
