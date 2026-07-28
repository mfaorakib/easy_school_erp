# Admission — Business-Logic Spec

> A public, no-login admission form so a prospective student's guardian can apply
> from the website; a staff/teacher then reviews and **confirms** it, which is the
> moment a real, fully-enrolled `AcademicCore` Student is created — with a photo
> and an admin-configurable formatted unique ID.

## Entities & tables

| # | Table | Model | Purpose |
|---|---|---|---|
| 1 | `admission_applications` | `AdmissionApplication` | A public application: applicant + guardian info, desired class, optional photo, `status` (pending → confirmed/rejected), reviewer + timestamp, and — once confirmed — the resulting `student_id` + `unique_id`. |

`students.unique_id` (new nullable string column, added alongside the existing
`admission_no` integer — nothing that reads `admission_no` breaks) holds the
formatted ID.

## Business rules

- **Two-stage flow.** The public form (`/admission/apply`) only ever creates a
  `pending` `AdmissionApplication` — never a `User`/`Student`. Nothing is "live"
  until a staff member reviews it.
- **Applicant self-tracking.** Every application gets a random `reference_no`
  (`APP-XXXXXXXX`), shown to the applicant on submit and usable at
  `/admission/status` to check pending/confirmed/rejected — no login needed.
- **Confirm = admit.** `AdmissionService::confirm()` does NOT duplicate
  admission logic — it hands off to `AcademicCore\Services\StudentAdmissionService::admit()`,
  the exact same routine the existing admin "walk-in" admission form
  (`AcademicCore\StudentController`) uses. A confirmed application always
  produces a real `User` (role `student`) + `Guardian`/`User` (role `parent`,
  when guardian info is present) + `Student` + `student_records` enrollment.
- **Photo** — the applicant may optionally attach one on the public form;
  confirming staff can override it with a different upload at review time
  (whichever is provided at confirm time wins; the applicant's photo is the
  fallback). Either way it lands on `Student.photo`.
- **Unique ID is admin-configurable, not hardcoded.** Super Admin/Admin sets a
  pattern (`admission.id_format` setting, e.g. `STU-{YYYY}-{SEQ:4}` →
  `STU-2026-0007`) at `/admission-admin/settings`. Tokens: `{YYYY}`/`{YY}`
  (year), `{SEQ:N}` (auto-incrementing, zero-padded to N digits). The pure
  templating lives in `Foundation\Support\IdPattern` so it's reusable; the
  sequence itself is `Student::whereNotNull('unique_id')->count() + 1` — same
  max+1 spirit as the legacy `admission_no`, just formatted. **Both** the walk-in
  admin form and the Admission confirm-flow generate IDs through the same
  `StudentAdmissionService::generateUniqueId()`, so every student gets a
  consistently-formatted ID regardless of entry point.
- **Reviewer is a User, not a Staff row.** `reviewed_by` is an FK to `users`
  (not `staff`) — deliberately, since the seeded super-admin account has no
  linked HR Staff profile, and any authenticated admin should be able to
  confirm/reject even without one.
- **Reject** requires a reason; **a pending application can only be reviewed
  once** — confirming or rejecting an already-reviewed application aborts
  (422), enforced in the service, not just the UI.
- **ID cards** (`Documents`) now prefer `unique_id` ("Student ID") over the
  legacy `admission_no` ("Admission No") when present, so newly admitted
  students' cards reflect the new scheme automatically.

## Screens

**Public (no auth):** `/admission/apply` (form) → `/admission/applied/{reference}`
(confirmation + reference number) · `/admission/status` (reference lookup).
**Admin (auth):** Applications list (filter by status) → detail (applicant info
+ Confirm form [class/section/photo] + Reject form [reason]) · ID-format
settings (pattern + live example, quick presets).

## Service surface

`AdmissionService`: `apply(data)` · `findByReference(ref)` · `confirm(application, placement, reviewer)` · `reject(application, reason, reviewer)`.
`StudentAdmissionService` (extended): `admit(data)` now also accepts `photo` +
`unique_id` (auto-generated when omitted) · `generateUniqueId()`.
