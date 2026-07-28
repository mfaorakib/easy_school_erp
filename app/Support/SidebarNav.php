<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Modules\Access\Enums\Role;
use Modules\Settings\Services\SidebarPositionService;
use Modules\Settings\Services\SidebarVisibilityService;

/**
 * Builds the admin sidebar's structure: ~30 leaf sub-groups (each a real,
 * route()-driven list of links) grouped into 10 umbrella sections. Display
 * ORDER (which section/sub-group/link shows first) and per-Admin-role
 * VISIBILITY can both be customized via the Sidebar Manager (see
 * SidebarPositionService / SidebarVisibilityService) — but the actual links,
 * routes, and active-state detection below are always the same regardless of
 * order or visibility, so no customization here can ever break navigation.
 */
class SidebarNav
{
    public function __construct(
        private readonly SidebarPositionService $positions,
        private readonly SidebarVisibilityService $visibility,
    ) {}

    /** Every leaf sub-group (label + real links), keyed by a stable slug. Each link is [slug, label, url, active]. */
    public function groups(): array
    {
        return [
            'academic' => ['label' => __('ui.academic'), 'links' => [
                ['classes', __('ui.classes'), route('academic.classes.index'), request()->routeIs('academic.classes.*')],
                ['sections', __('ui.sections'), route('academic.sections.index'), request()->routeIs('academic.sections.*')],
                ['subjects', __('ui.subjects'), route('academic.subjects.index'), request()->routeIs('academic.subjects.*')],
                ['subject_assign', __('ui.subject_assign'), route('academic.subject-assignments.index'), request()->routeIs('academic.subject-assignments.*')],
                ['class_teacher', __('ui.class_teacher'), route('academic.class-teachers.index'), request()->routeIs('academic.class-teachers.*')],
            ]],
            'students' => ['label' => __('ui.students'), 'links' => [
                ['all_students', __('ui.all_students'), route('academic.students.index'), request()->routeIs('academic.students.index')],
                ['admission', __('ui.admission'), route('academic.students.create'), request()->routeIs('academic.students.create')],
                ['promotion', __('ui.promotion'), route('academic.promotion.create'), request()->routeIs('academic.promotion.*')],
                ['student_attendance', __('ui.student_attendance'), route('attendance.student.index'), request()->routeIs('attendance.student.*')],
                ['student_leave', __('ui.student_leave'), route('leave.student.index'), request()->routeIs('leave.student.*')],
                ['document_types', __('ui.document_types'), route('academic.document-types.index'), request()->routeIs('academic.document-types.*')],
            ]],
            'admission_applications' => ['label' => __('ui.admission_applications'), 'links' => [
                ['applications', __('ui.applications'), route('admission.admin.applications.index'), request()->routeIs('admission.admin.applications.*')],
                ['id_format', __('ui.id_format'), route('admission.admin.settings.edit'), request()->routeIs('admission.admin.settings.*')],
                ['apply_now', __('ui.apply_now').' ('.__('ui.view_site').')', route('admission.apply'), false],
            ]],
            'timetable' => ['label' => __('ui.timetable'), 'links' => [
                ['class_routine', __('ui.class_routine'), route('timetable.routine'), request()->routeIs('timetable.routine')],
                ['timetable_builder', __('ui.timetable_builder'), route('timetable.builder'), request()->routeIs('timetable.builder')],
                ['teacher_timetable', __('ui.teacher_timetable'), route('timetable.teacher'), request()->routeIs('timetable.teacher')],
                ['periods', __('ui.periods'), route('timetable.periods.index'), request()->routeIs('timetable.periods.*')],
                ['classrooms', __('ui.classrooms'), route('timetable.classrooms.index'), request()->routeIs('timetable.classrooms.*')],
            ]],
            'examination' => ['label' => __('ui.examination'), 'links' => [
                ['exams', __('ui.exams'), route('exam.exams.index'), request()->routeIs('exam.exams.*')],
                ['exam_types', __('ui.exam_types'), route('exam.types.index'), request()->routeIs('exam.types.*')],
                ['grades', __('ui.grades'), route('exam.grades.index'), request()->routeIs('exam.grades.*')],
                ['exam_schedule', __('ui.exam_schedule'), route('exam.schedules.index'), request()->routeIs('exam.schedules.*')],
                ['marks_entry', __('ui.marks_entry'), route('exam.marks.index'), request()->routeIs('exam.marks.*')],
                ['results', __('ui.results'), route('exam.results.index'), request()->routeIs('exam.results.*')],
                ['seat_plan', __('ui.seat_plan'), route('exam.seat_plan.index'), request()->routeIs('exam.seat_plan.*')],
            ]],
            'online_exam' => ['label' => __('ui.online_exam'), 'links' => [
                ['exams', __('ui.exams'), route('onlineexam.exams.index'), request()->routeIs('onlineexam.exams.*')],
                ['question_bank', __('ui.question_bank'), route('onlineexam.questions.index'), request()->routeIs('onlineexam.questions.*')],
                ['question_groups', __('ui.question_groups'), route('onlineexam.groups.index'), request()->routeIs('onlineexam.groups.*')],
                ['results', __('ui.results'), route('onlineexam.results.index'), request()->routeIs('onlineexam.results.*')],
                ['my_exams', __('ui.my_exams'), route('onlineexam.student.index'), request()->routeIs('onlineexam.student.*')],
            ]],
            'hr' => ['label' => __('ui.staff'), 'links' => [
                ['staff', __('ui.staff'), route('hr.staff.index'), request()->routeIs('hr.staff.*')],
                ['designations', __('ui.designations'), route('hr.designations.index'), request()->routeIs('hr.designations.*')],
                ['departments', __('ui.departments'), route('hr.departments.index'), request()->routeIs('hr.departments.*')],
                ['resignations', __('ui.resignations'), route('hr.resignations.index'), request()->routeIs('hr.resignations.*')],
                ['staff_attendance', __('ui.staff_attendance'), route('attendance.staff.index'), request()->routeIs('attendance.staff.*')],
            ]],
            'leave' => ['label' => __('ui.leave'), 'links' => [
                ['apply_leave', __('ui.apply_leave'), route('leave.applications.create'), request()->routeIs('leave.applications.create')],
                ['leave_applications', __('ui.leave_applications'), route('leave.applications.index'), request()->routeIs('leave.applications.index')],
                ['approvals', __('ui.approvals'), route('leave.approvals.index'), request()->routeIs('leave.approvals.*')],
                ['leave_balance', __('ui.leave_balance'), route('leave.balance'), request()->routeIs('leave.balance')],
                ['leave_types', __('ui.leave_types'), route('leave.types.index'), request()->routeIs('leave.types.*')],
                ['evaluations', __('ui.evaluations'), route('leave.evaluations.index'), request()->routeIs('leave.evaluations.*')],
                ['shifts', __('ui.shifts'), route('leave.shifts.index'), request()->routeIs('leave.shifts.*')],
                ['assign_shifts', __('ui.assign_shifts'), route('leave.shifts.assign'), request()->routeIs('leave.shifts.assign')],
            ]],
            'payroll' => ['label' => __('ui.payroll'), 'links' => [
                ['salary_templates', __('ui.salary_templates'), route('payroll.templates.index'), request()->routeIs('payroll.templates.*')],
                ['staff_salaries', __('ui.staff_salaries'), route('payroll.staff.index'), request()->routeIs('payroll.staff.*')],
                ['generate_payroll', __('ui.generate_payroll'), route('payroll.generate.create'), request()->routeIs('payroll.generate.*')],
                ['payslips', __('ui.payslips'), route('payroll.payslips.index'), request()->routeIs('payroll.payslips.*')],
                ['salary_advances', __('ui.salary_advances'), route('payroll.advances.index'), request()->routeIs('payroll.advances.*')],
                ['bank_advice', __('ui.bank_advice'), route('payroll.payslips.bankAdvice'), request()->routeIs('payroll.payslips.bankAdvice')],
                ['summary', __('ui.summary'), route('payroll.summary'), request()->routeIs('payroll.summary')],
            ]],
            'fees' => ['label' => __('ui.fees'), 'links' => [
                ['fee_collect', __('ui.fee_collect'), route('fees.collect.index'), request()->routeIs('fees.collect.*')],
                ['confirm_payment', __('ui.confirm_payment'), route('fees.intents.index'), request()->routeIs('fees.intents.*')],
                ['fee_masters', __('ui.fee_masters'), route('fees.masters.index'), request()->routeIs('fees.masters.*')],
                ['fee_assign', __('ui.fee_assign'), route('fees.assign.index'), request()->routeIs('fees.assign.*')],
                ['fee_groups', __('ui.fee_groups'), route('fees.groups.index'), request()->routeIs('fees.groups.*')],
                ['fee_types', __('ui.fee_types'), route('fees.types.index'), request()->routeIs('fees.types.*')],
                ['fee_discounts', __('ui.fee_discounts'), route('fees.discounts.index'), request()->routeIs('fees.discounts.*')],
                ['student_fines', __('ui.student_fines'), route('fees.fines.index'), request()->routeIs('fees.fines.*')],
            ]],
            'accounting' => ['label' => __('ui.accounting'), 'links' => [
                ['income', __('ui.income'), route('accounting.transactions.index', ['type' => 'income']), request()->routeIs('accounting.transactions.*')],
                ['expense', __('ui.expense'), route('accounting.transactions.index', ['type' => 'expense']), false],
                ['account_heads', __('ui.account_heads'), route('accounting.heads.index'), request()->routeIs('accounting.heads.*')],
                ['bank_accounts', __('ui.bank_accounts'), route('accounting.banks.index'), request()->routeIs('accounting.banks.*')],
                ['transfers', __('ui.transfers'), route('accounting.transfers.index'), request()->routeIs('accounting.transfers.*')],
                ['summary', __('ui.summary'), route('accounting.summary'), request()->routeIs('accounting.summary')],
            ]],
            'wallet' => ['label' => __('ui.wallet'), 'links' => [
                ['deposit_withdraw', __('ui.deposit_withdraw'), route('wallet.transactions.index'), request()->routeIs('wallet.transactions.*')],
                ['wallets', __('ui.wallets'), route('wallet.wallets.index'), request()->routeIs('wallet.wallets.*')],
                ['history', __('ui.history'), route('wallet.history.index'), request()->routeIs('wallet.history.*')],
            ]],
            'inventory' => ['label' => __('ui.inventory'), 'links' => [
                ['items', __('ui.items'), route('inventory.items.index'), request()->routeIs('inventory.items.*')],
                ['receive', __('ui.receive'), route('inventory.receive.index'), request()->routeIs('inventory.receive.*')],
                ['issue', __('ui.issue'), route('inventory.issue.index'), request()->routeIs('inventory.issue.*')],
                ['sell', __('ui.sell'), route('inventory.sell.index'), request()->routeIs('inventory.sell.*')],
                ['suppliers', __('ui.suppliers'), route('inventory.suppliers.index'), request()->routeIs('inventory.suppliers.*')],
                ['item_stores', __('ui.item_stores'), route('inventory.stores.index'), request()->routeIs('inventory.stores.*')],
                ['item_categories', __('ui.item_categories'), route('inventory.categories.index'), request()->routeIs('inventory.categories.*')],
            ]],
            'library' => ['label' => __('ui.library'), 'links' => [
                ['issue_return', __('ui.issue_return'), route('library.issue.index'), request()->routeIs('library.issue.*')],
                ['book_issue_history', __('ui.book_issue_history'), route('library.issue.history'), request()->routeIs('library.issue.history')],
                ['books', __('ui.books'), route('library.books.index'), request()->routeIs('library.books.*')],
                ['book_categories', __('ui.book_categories'), route('library.categories.index'), request()->routeIs('library.categories.*')],
                ['library_members', __('ui.library_members'), route('library.members.index'), request()->routeIs('library.members.*')],
            ]],
            'transport' => ['label' => __('ui.transport'), 'links' => [
                ['transport_routes', __('ui.transport_routes'), route('transport.routes.index'), request()->routeIs('transport.routes.*')],
                ['vehicles', __('ui.vehicles'), route('transport.vehicles.index'), request()->routeIs('transport.vehicles.*')],
                ['assign_vehicles', __('ui.assign_vehicles'), route('transport.assign-vehicles.index'), request()->routeIs('transport.assign-vehicles.*')],
                ['student_transport', __('ui.student_transport'), route('transport.students.index'), request()->routeIs('transport.students.*')],
            ]],
            'dormitory' => ['label' => __('ui.dormitory'), 'links' => [
                ['room_allocation', __('ui.room_allocation'), route('dormitory.students.index'), request()->routeIs('dormitory.students.*')],
                ['rooms', __('ui.rooms'), route('dormitory.rooms.index'), request()->routeIs('dormitory.rooms.*')],
                ['dormitories', __('ui.dormitories'), route('dormitory.dormitories.index'), request()->routeIs('dormitory.dormitories.*')],
                ['room_types', __('ui.room_types'), route('dormitory.room-types.index'), request()->routeIs('dormitory.room-types.*')],
            ]],
            'behaviour' => ['label' => __('ui.behaviour'), 'links' => [
                ['behaviour_record', __('ui.behaviour_record'), route('behaviour.records.index'), request()->routeIs('behaviour.records.*')],
                ['behaviour_report', __('ui.behaviour_report'), route('behaviour.report.index'), request()->routeIs('behaviour.report.*')],
                ['behaviour_types', __('ui.behaviour_types'), route('behaviour.types.index'), request()->routeIs('behaviour.types.*')],
            ]],
            'front_office' => ['label' => __('ui.front_office'), 'links' => [
                ['enquiries', __('ui.enquiries'), route('frontoffice.enquiries.index'), request()->routeIs('frontoffice.enquiries.*')],
                ['visitors', __('ui.visitors'), route('frontoffice.visitors.index'), request()->routeIs('frontoffice.visitors.*')],
                ['postal', __('ui.postal'), route('frontoffice.postal.index'), request()->routeIs('frontoffice.postal.*')],
                ['complaints', __('ui.complaints'), route('frontoffice.complaints.index'), request()->routeIs('frontoffice.complaints.*')],
                ['complaint_types', __('ui.complaint_types'), route('frontoffice.complaintTypes.index'), request()->routeIs('frontoffice.complaintTypes.*')],
                ['call_log', __('ui.call_log'), route('frontoffice.callLogs.index'), request()->routeIs('frontoffice.callLogs.*')],
            ]],
            'homework' => ['label' => __('ui.homework'), 'links' => [
                ['homeworks', __('ui.homeworks'), route('homework.homeworks.index'), request()->routeIs('homework.homeworks.*')],
                ['evaluate', __('ui.evaluate'), route('homework.evaluate.index'), request()->routeIs('homework.evaluate.*')],
                ['overview', __('ui.overview'), route('homework.overview.index'), request()->routeIs('homework.overview.*')],
            ]],
            'lesson' => ['label' => __('ui.lesson'), 'links' => [
                ['lessons', __('ui.lessons'), route('lesson.lessons.index'), request()->routeIs('lesson.lessons.*')],
                ['topics', __('ui.topics'), route('lesson.topics.index'), request()->routeIs('lesson.topics.*')],
                ['lesson_progress', __('ui.lesson_progress'), route('lesson.overview.index'), request()->routeIs('lesson.overview.*')],
            ]],
            'download_center' => ['label' => __('ui.download_center'), 'links' => [
                ['downloads', __('ui.downloads'), route('downloadcenter.center.index'), request()->routeIs('downloadcenter.center.*')],
                ['contents', __('ui.contents'), route('downloadcenter.contents.index'), request()->routeIs('downloadcenter.contents.*')],
                ['content_types', __('ui.content_types'), route('downloadcenter.types.index'), request()->routeIs('downloadcenter.types.*')],
            ]],
            'communication' => ['label' => __('ui.nav_notices_events'), 'links' => [
                ['notice_board', __('ui.notice_board'), route('communication.board.index'), request()->routeIs('communication.board.*')],
                ['notices', __('ui.notices'), route('communication.notices.index'), request()->routeIs('communication.notices.*')],
                ['events', __('ui.events'), route('communication.events.index'), request()->routeIs('communication.events.*')],
            ]],
            'chat' => ['label' => __('ui.chat'), 'links' => [
                ['messages', __('ui.messages'), route('chat.index'), request()->routeIs('chat.index') || request()->routeIs('chat.show')],
                ['groups', __('ui.groups'), route('chat.groups.index'), request()->routeIs('chat.groups.*')],
                ['contacts', __('ui.contacts'), route('chat.contacts'), request()->routeIs('chat.contacts')],
            ]],
            'reports' => ['label' => __('ui.reports'), 'links' => [
                ['student_report', __('ui.student_report'), route('reports.students'), request()->routeIs('reports.students')],
                ['guardian_report', __('ui.guardian_report'), route('reports.guardians'), request()->routeIs('reports.guardians')],
                ['attendance_report', __('ui.attendance_report'), route('reports.attendance'), request()->routeIs('reports.attendance')],
                ['fees_collection_report', __('ui.fees_collection_report'), route('reports.fees.collection'), request()->routeIs('reports.fees.collection')],
                ['fees_due_report', __('ui.fees_due_report'), route('reports.fees.due'), request()->routeIs('reports.fees.due')],
                ['wallet_report', __('ui.wallet_report'), route('reports.wallet'), request()->routeIs('reports.wallet')],
                ['exam_result_report', __('ui.exam_result_report'), route('reports.exam.results'), request()->routeIs('reports.exam.results')],
                ['merit_list_report', __('ui.merit_list_report'), route('reports.exam.merit'), request()->routeIs('reports.exam.merit')],
            ]],
            'documents' => ['label' => __('ui.documents'), 'links' => [
                ['id_cards', __('ui.id_cards'), route('documents.idCards.index'), request()->routeIs('documents.idCards.*')],
                ['certificates', __('ui.certificates'), route('documents.certificates.index'), request()->routeIs('documents.certificates.*')],
                ['id_card_templates', __('ui.id_card_templates'), route('documents.idCardTemplates.index'), request()->routeIs('documents.idCardTemplates.*')],
                ['certificate_templates', __('ui.certificate_templates'), route('documents.certificateTemplates.index'), request()->routeIs('documents.certificateTemplates.*')],
            ]],
            'print_center' => ['label' => __('ui.print_center'), 'links' => [
                ['marksheet', __('ui.marksheet'), route('printing.marksheet'), request()->routeIs('printing.marksheet')],
                ['tabulation', __('ui.tabulation'), route('printing.tabulation'), request()->routeIs('printing.tabulation')],
                ['progress_card', __('ui.progress_card'), route('printing.progress'), request()->routeIs('printing.progress')],
                ['fee_statement', __('ui.fee_statement'), route('printing.invoice'), request()->routeIs('printing.invoice')],
                ['seat_plan', __('ui.seat_plan'), route('printing.seat_plan'), request()->routeIs('printing.seat_plan*')],
                ['print_center', __('ui.print_center'), route('printing.index'), request()->routeIs('printing.index')],
            ]],
            'builder' => ['label' => __('ui.builder'), 'links' => [
                ['pages', __('ui.pages'), route('builder.pages.index'), request()->routeIs('builder.pages.*') || request()->routeIs('builder.blocks.*')],
                ['menus', __('ui.menus'), route('builder.menus.index'), request()->routeIs('builder.menus.*')],
                ['sliders', __('ui.sliders'), route('builder.sliders.index'), request()->routeIs('builder.sliders.*')],
                ['testimonials', __('ui.testimonials'), route('builder.testimonials.index'), request()->routeIs('builder.testimonials.*')],
                ['messages_inbox', __('ui.messages_inbox'), route('builder.messages.index'), request()->routeIs('builder.messages.*')],
                ['site_settings', __('ui.site_settings'), route('builder.settings.edit'), request()->routeIs('builder.settings.*')],
                ['view_site', __('ui.view_site'), url('/'), false],
            ]],
            'settings' => ['label' => __('ui.settings'), 'links' => [
                ['general_settings', __('ui.general_settings'), route('settings.general'), request()->routeIs('settings.general') || request()->routeIs('settings.index')],
                ['localization', __('ui.localization'), route('settings.localization'), request()->routeIs('settings.localization')],
                ['email_settings', __('ui.email_settings'), route('settings.email'), request()->routeIs('settings.email')],
                ['sms_settings', __('ui.sms_settings'), route('settings.sms'), request()->routeIs('settings.sms')],
                ['appearance', __('ui.appearance'), route('settings.appearance'), request()->routeIs('settings.appearance')],
                ['holidays', __('ui.holidays'), route('settings.holidays.index'), request()->routeIs('settings.holidays.*')],
                ['payment_methods', __('ui.payment_methods'), route('settings.paymentMethods.index'), request()->routeIs('settings.paymentMethods.*')],
                ['sidebar_manager', __('ui.sidebar_manager'), route('settings.sidebarManager.edit'), request()->routeIs('settings.sidebarManager.*')],
            ]],
            'roles' => ['label' => __('ui.roles'), 'links' => [
                ['roles', __('ui.roles'), route('access.roles.index'), request()->routeIs('access.roles.*')],
                ['users', __('ui.users'), route('access.users.index'), request()->routeIs('access.users.*')],
            ]],
        ];
    }

    /** The umbrella sections in their built-in DEFAULT order (before any customization). */
    public function defaultSections(): array
    {
        return [
            ['key' => 'academic_affairs', 'label' => __('ui.nav_academic'), 'icon' => 'fa-solid fa-school', 'groups' => ['academic', 'students', 'admission_applications', 'timetable']],
            ['key' => 'examinations', 'label' => __('ui.nav_examinations'), 'icon' => 'fa-solid fa-clipboard-check', 'groups' => ['examination', 'online_exam']],
            ['key' => 'teaching_content', 'label' => __('ui.nav_teaching'), 'icon' => 'fa-solid fa-book-open', 'groups' => ['homework', 'lesson', 'download_center']],
            ['key' => 'human_resource', 'label' => __('ui.hr'), 'icon' => 'fa-solid fa-users', 'groups' => ['hr', 'leave', 'payroll']],
            ['key' => 'finance', 'label' => __('ui.nav_finance'), 'icon' => 'fa-solid fa-sack-dollar', 'groups' => ['fees', 'accounting', 'wallet']],
            ['key' => 'store', 'label' => __('ui.inventory'), 'icon' => 'fa-solid fa-boxes-stacked', 'groups' => ['inventory']],
            ['key' => 'student_services', 'label' => __('ui.nav_student_services'), 'icon' => 'fa-solid fa-graduation-cap', 'groups' => ['library', 'transport', 'dormitory', 'behaviour']],
            ['key' => 'communication', 'label' => __('ui.communication'), 'icon' => 'fa-solid fa-bullhorn', 'groups' => ['communication', 'chat', 'front_office']],
            ['key' => 'reports', 'label' => __('ui.reports'), 'icon' => 'fa-solid fa-chart-bar', 'groups' => ['reports']],
            ['key' => 'documents_print', 'label' => __('ui.nav_documents_print'), 'icon' => 'fa-solid fa-print', 'groups' => ['documents', 'print_center']],
            ['key' => 'website', 'label' => __('ui.nav_website'), 'icon' => 'fa-solid fa-globe', 'groups' => ['builder']],
            ['key' => 'system', 'label' => __('ui.nav_system'), 'icon' => 'fa-solid fa-gear', 'groups' => ['settings', 'roles']],
        ];
    }

    /** Admin-created sections beyond the 10 built-in ones, each starting with no sub-groups (nothing assigned to it yet). */
    public function customSections(): array
    {
        return \Modules\Settings\Models\SidebarSection::orderBy('id')->get()->map(fn ($s) => [
            'key' => $s->key, 'label' => $s->label, 'icon' => $s->icon, 'groups' => [], 'is_custom' => true,
        ])->all();
    }

    /** Which section (built-in or custom) a sub-group belongs to by DEFAULT, before any admin override. */
    private function defaultSectionKeyFor(string $itemKey): ?string
    {
        foreach ($this->defaultSections() as $section) {
            if (in_array($itemKey, $section['groups'], true)) {
                return $section['key'];
            }
        }

        return null;
    }

    /**
     * Every section (10 built-in + any custom ones), each with its 'groups'
     * list recomputed from CURRENT assignments — a sub-group with no admin
     * override sits under its default section exactly as before; one with an
     * override sits wherever it was moved to. An override pointing at a
     * since-deleted custom section falls back to the default placement
     * automatically (defence against a dangling reference), on top of
     * destroySection() already cleaning up its own assignment rows.
     */
    public function allSections(): array
    {
        // Plain array, not a Collection — mutating a nested ['groups'][] on a
        // Collection's array-access item triggers PHP's "indirect
        // modification of overloaded property" (offsetGet doesn't return by
        // reference), so this has to stay a real array to append into.
        $sections = [];
        foreach ($this->defaultSections() as $s) {
            // Explicit overwrite, NOT array-union (`+` keeps the LEFT side's
            // value on a key collision, so `$s + ['groups' => []]` would
            // silently keep defaultSections()'s already-populated 'groups'
            // instead of resetting it — the reassignment loop below would
            // then append onto that stale list, duplicating every item and
            // leaving moved ones behind in their old section too).
            $s['groups'] = [];
            $sections[$s['key']] = $s + ['is_custom' => false];
        }
        foreach ($this->customSections() as $s) {
            $s['groups'] = [];
            $sections[$s['key']] = $s;
        }

        $overrides = \Modules\Settings\Models\SidebarGroupAssignment::pluck('section_key', 'item_key');

        foreach (array_keys($this->groups()) as $itemKey) {
            $targetKey = $overrides->get($itemKey) ?? $this->defaultSectionKeyFor($itemKey);
            if (! $targetKey || ! isset($sections[$targetKey])) {
                $targetKey = $this->defaultSectionKeyFor($itemKey);
            }
            if ($targetKey && isset($sections[$targetKey])) {
                $sections[$targetKey]['groups'][] = $itemKey;
            }
        }

        return array_values($sections);
    }

    /** All sections in the admin-CUSTOMIZED order (falls back to default order for anything unconfigured). */
    public function orderedSections(): array
    {
        $sections = $this->allSections();
        $byKey = collect($sections)->keyBy('key');
        $orderedKeys = $this->positions->orderedKeys('', array_column($sections, 'key'));

        return collect($orderedKeys)->map(fn ($k) => $byKey[$k])->values()->all();
    }

    /** One section's own sub-group keys, in the admin-CUSTOMIZED order (falls back to that section's default order). */
    public function orderedGroupKeys(array $section): array
    {
        return $this->positions->orderedKeys($section['key'], $section['groups']);
    }

    /** One group's own links, in the admin-CUSTOMIZED order (falls back to the built-in array order). Each $links entry is [slug, label, url, active]. */
    public function orderedLinks(string $groupKey, array $links): array
    {
        $byKey = collect($links)->keyBy(fn ($l) => $l[0]);
        $orderedKeys = $this->positions->orderedKeys($groupKey, array_column($links, 0));

        return collect($orderedKeys)->map(fn ($k) => $byKey[$k])->values()->all();
    }

    /**
     * True if $itemKey (a section key, a group key, or a link slug) is
     * hidden from the CURRENTLY authenticated user — only ever true for a
     * user holding the Admin role (Super Admin always sees everything, and
     * no other role is affected by this toggle today). Used only by the real
     * sidebar layout; the Sidebar Manager page itself must keep showing
     * every item unfiltered so Super Admin can find and un-hide them.
     */
    public function isHiddenForCurrentUser(string $itemKey): bool
    {
        $user = Auth::user();
        if (! $user || $user->isSuperAdmin() || ! $user->hasRole(Role::Admin->value)) {
            return false;
        }

        return $this->visibility->isHidden(Role::Admin->value, $itemKey);
    }

    /**
     * Render-ready sub-groups for one section: admin-ordered, with any
     * Admin-hidden groups (and, within each surviving group, any
     * Admin-hidden links) removed. The Sidebar Manager itself does NOT use
     * this — it calls orderedGroupKeys()/groups() directly and unfiltered.
     */
    public function renderableSubgroups(array $section): \Illuminate\Support\Collection
    {
        $groupsByKey = $this->groups();

        return collect($this->orderedGroupKeys($section))
            ->reject(fn ($k) => $this->isHiddenForCurrentUser($k))
            ->map(function ($k) use ($groupsByKey) {
                if (! isset($groupsByKey[$k])) {
                    return null;
                }

                $group = array_merge(['key' => $k], $groupsByKey[$k]);
                $group['links'] = collect($this->orderedLinks($k, $group['links']))
                    ->reject(fn ($l) => $this->isHiddenForCurrentUser($l[0]))
                    ->values()
                    ->all();

                return $group;
            })
            ->filter()
            ->values();
    }
}
