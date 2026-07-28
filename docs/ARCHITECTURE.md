# EasySchool ERP — Architecture

Clean rebuild of the reference system. **Same business logic, new design, better structure.**

## Stack
- Laravel 12 / PHP 8.2, MySQL (`easyschool_erp`)
- `nwidart/laravel-modules` — every feature is a self-contained module
- `spatie/laravel-permission` — roles & permissions (replaces hand-rolled `infix_roles`/`assign_permissions`)

## Why this is better than the reference system
| the reference system problem | EasySchool fix |
|---|---|
| ~200 `Sm*` models loose in `app/` root | Models live inside their module: `Modules/<X>/app/Models` |
| Fat controllers with business logic inline | Thin controllers → **Service** / **Action** classes hold logic |
| ~100-column `sm_general_settings` | Slim settings + normalized `settings` key/value + `feature_flags` |
| Multi-tenant `school_id` everywhere (unused single-school) | Dropped; single implicit school |
| SaaS/subdomain/purchase-code coupling | Removed (no SaaS billing) |
| Raw `Sm` table prefixes, typo columns (`twiteer_url`, `merital_status`) | Clean snake_case names |

## Kept from the reference system (core business logic — do NOT change)
- **Academic-year scoping**: operational tables carry `academic_id`; a global scope auto-filters to the active year (`getAcademicId()` equivalent). Active year pointer stored in settings.
- **Year cloning**: creating a new academic year clones structure (classes, sections, subjects, grades) into it.
- **Enrollment via history table** `student_records` (is_promote/is_default) — not just inline on students.
- **Promotion** = new `student_records` row for target year + audit snapshot, old record `is_promote=1`.
- **Roles** (fixed IDs): 1 Super admin, 2 Student, 3 Parent, 4 Teacher, 5 Admin, 6 Accountant, 7 Receptionist, 8 Librarian, 9 Driver.
- Single `users` table for all actors; `staff`/`student`/`parent` are profile extensions (`user_id`).

## Folder convention (per module)
```
Modules/<Name>/
  app/
    Models/            Http/Controllers/   Http/Requests/
    Services/          Actions/            Providers/
  database/migrations/  database/seeders/
  resources/views/      routes/{web.php,api.php}
  config/               tests/
```
Shared cross-module code (base model with academic scope, enums, helpers) lives in `app/Core/`.

## Module build order (each on user's go)
Foundation (auth+roles+settings+session) → Academic Core (class/section/subject/student/staff/parent)
→ Attendance → Fees → Exam → Homework/Lesson → Wallet → Inventory → Library → Transport → Communication (Notice/Chat)
→ Builder (page/menu/settings builder). **Skipped: SaaS subscription/billing.**

## Reference
Business-logic specs reverse-engineered from the reference system: `docs/business-logic/01..04`.
Rule: **the reference system live code is the source of truth for logic; the `andiedu.sql` dump is only a data-shape reference** (they differ — e.g. `infix_roles` vs `roles`).
