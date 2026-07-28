# 13 — Homework Business Logic

Source: reverse-engineered from the reference system (Laravel school-management app).
This document captures the observed behavior so the Homework module can be faithfully
rebuilt in easyschool-erp. Where the reference carries multi-tenant / university / LMS
baggage, a clean single-school rebuild recommendation is given at the end.

---

## 1. Entities & Tables

### 1.1 `sm_homeworks` — the homework assignment (header)

One row per **(class, section, subject, homework)**. This is the teacher's assignment.

| Column            | Type            | Meaning                                                             |
| ----------------- | --------------- | ------------------------------------------------------------------- |
| `id`              | int PK          |                                                                     |
| `class_id`        | int             | Target class                                                        |
| `section_id`      | int             | Target section (one row per section — see assign flow)              |
| `subject_id`      | int             | Target subject                                                      |
| `shift_id`        | int nullable    | Optional shift (only when shift feature enabled)                    |
| `homework_date`   | date            | Date homework is assigned / given                                   |
| `submission_date` | date            | Due date                                                            |
| `evaluation_date` | date nullable   | Set once the teacher evaluates (bulk-stamped at evaluation time)    |
| `marks`           | varchar         | **Total/maximum** marks for the homework (stored as string)         |
| `description`     | varchar(500)    | Instructions / body text                                            |
| `file`            | varchar/JSON    | Attached files. Model casts to `array`; multiple files supported    |
| `evaluated_by`    | int nullable    | User id of the teacher/staff who evaluated                          |
| `created_by`      | int             | User id of the teacher/staff who created it (**this is "teacher"**) |
| `updated_by`      | int             |                                                                     |
| `active_status`   | tinyint (1)     |                                                                     |
| `academic_id`     | int             | **Academic-year scoped** (default 1)                                |
| `school_id`       | int             | Multi-tenant id (default 1) — **drop in rebuild**                   |
| `record_id`       | int nullable    | Legacy, unused                                                      |
| `course_id`, `lesson_id`, `chapter_id`, `student_ids` | LMS-module columns (nullable). `student_ids` is a JSON snapshot only added/used when the LMS module is active. **Out of scope for core rebuild.** |
| `created_at` / `updated_at` | timestamps |                                                        |

Notes:
- **There is no dedicated `teacher_id` / `staff_id` column.** The teacher is whoever
  created the row: `created_by` (a `users.id`). The evaluator is `evaluated_by`.
  A `SmStaff` record is looked up via `user_id` only to scope which classes a teacher
  may pick from — it is not stored on the homework.
- **Academic-year scoped:** yes. A global scope (`StatusAcademicSchoolScope`) filters
  every query by `academic_id` (current academic year) and `school_id`. On create,
  `academic_id = getAcademicId()` and `school_id = auth user's school_id`.
- `marks` on the header is the **maximum** mark; obtained marks live per-student.

### 1.2 `sm_homework_students` — per-student evaluation record

One row per **(homework, student)**, created **only at evaluation time** (not at assign
time). Records that a specific student completed the homework and their result.

| Column             | Type         | Meaning                                                        |
| ------------------ | ------------ | -------------------------------------------------------------- |
| `id`               | int PK       |                                                                |
| `homework_id`      | int          | FK → `sm_homeworks.id`                                         |
| `student_id`       | int          | FK → student                                                  |
| `complete_status`  | varchar      | **`'C'` = Complete**, `'I'` (or other/absent row) = Incomplete |
| `marks`            | varchar      | **Obtained** marks for this student                            |
| `teacher_comments` | varchar(255) | Free-text feedback per student                                 |
| `created_by`       | int          | Evaluating user id                                            |
| `updated_by`       | int          |                                                                |
| `active_status`    | tinyint      |                                                                |
| `academic_id`      | int          | Academic-year scoped                                          |
| `school_id`        | int          | Multi-tenant — **drop in rebuild**                            |
| `created_at`       | timestamp    | Effectively the evaluation date for this student              |

Notes:
- **No `evaluation_date` column here** — the per-student evaluation timestamp is
  `created_at`; the homework-level `evaluation_date` on `sm_homeworks` is stamped once
  for the whole batch.
- Uniqueness is enforced procedurally, not by a DB constraint: the save flow **deletes
  any existing row** for `(homework_id, student_id)` then inserts a fresh one, so there
  is effectively one row per pair.
- Completion percentage for a homework = count of rows with `complete_status = 'C'`
  divided by the number of students currently enrolled in that class+section.

### 1.3 `sm_upload_homework_contents` — student submission (optional)

One row per student upload against a homework. Populated from the student/parent panel,
not by the teacher. Included here for completeness.

| Column        | Type | Meaning                              |
| ------------- | ---- | ------------------------------------ |
| `id`          | PK   |                                      |
| `homework_id` | int  | FK → homework                        |
| `student_id`  | int  | Submitting student                   |
| `description` | text | Student's note                       |
| `file`        | text | JSON list of uploaded files          |
| `created_at`  | ts   | Submission date                      |
| plus `academic_id`, `school_id`, `created_by`, `updated_by` |

`SmStudentHomework.php` in the reference is an **empty stub model** (no table, no logic)
and can be ignored.

---

## 2. Assign Flow (teacher creates homework)

1. Teacher opens "Add Homework". Class dropdown is limited to the teacher's assigned
   classes (`SmStaff.classes` via `user_id`); admins see all classes. Section and
   subject cascade from the chosen class (subjects come from `SmAssignSubject` for that
   class/section).
2. Teacher fills: class, **section(s)** (multi-select allowed), subject, homework_date,
   submission_date, marks (max), description, and optionally one or more attachment files.
3. On save (`saveHomeworkData`):
   - Uploaded files are moved to `uploads/homeworkcontent/` and their names collected
     into an array stored in `file`.
   - **One `sm_homeworks` row is created per selected section.** If the teacher picks
     3 sections, 3 homework rows are inserted (same subject/dates/marks/description/files).
   - Each row is stamped `created_by = current user`, `academic_id = current year`,
     `school_id`, `shift_id` (if shift enabled).
   - **No per-student rows are created here.** The roster is NOT materialized at assign
     time. The set of students is derived later, live, from current enrollment.
4. Notifications are sent to every enrolled student (and parents) of the target
   class+section that a homework was assigned.

Key rule: **the class+section+subject defines the audience; the per-student list is
implicit (whoever is enrolled), resolved at evaluation/report time — never snapshotted
at creation** (except in the separate LMS-module path, which snapshots `student_ids`).

---

## 3. Evaluation Flow (teacher marks each student)

1. Teacher opens the evaluation screen for a specific homework
   (`evaluationHomework($class_id, $section_id, $homework_id)`).
2. **The student roster is generated on the fly from current enrollment** for that
   class+section (`classSectionStudent`, i.e. active students in that class/section for
   the current academic year) — NOT from any stored roster. So students who enrolled
   after the homework was created still appear; students who left drop off.
3. For each student the screen shows a row where the teacher sets:
   - completion status → `homework_status[student_id]` = `'C'` (complete) or `'I'`,
   - obtained `marks[i]`,
   - optional `teacher_comments[student_id]`.
4. On save (`saveHomeworkEvaluationData`), for each submitted student:
   - **Delete** any existing `sm_homework_students` row for `(homework_id, student_id)`
     (idempotent re-evaluation), then **insert** a new one with `complete_status`,
     `marks`, `teacher_comments`, `created_by = evaluator`, `academic_id`, `school_id`.
   - (If LMS active and status is `'C'`, it also marks the linked lesson complete — out
     of core scope.)
5. After the loop, the parent `sm_homeworks` row is stamped once:
   `evaluation_date = today`, `evaluated_by = current user`.
6. Notifications ("homework evaluated") are sent to the students and parents.

Reporting (`viewEvaluationReport`, `homeworkReportSearch`) joins the live enrolled
students against `sm_homework_students`: a student with a `'C'` row is "Completed",
a student with no row (or non-`C`) is "Not Complete"; obtained marks and comments come
from the per-student row, total marks and dates from the header.

---

## 4. Clean Single-School Rebuild Recommendation

Target: two core tables, live-enrollment roster, no multi-tenant column, keep
academic-year scoping. Drop the University/LMS/shift branches from core (add later as
optional features if ever needed).

### 4.1 `homeworks` (header)

```
id                 PK
class_id           FK classes
section_id         FK sections
subject_id         FK subjects
teacher_id         FK staff        -- explicit; still record created_by user separately if desired
homework_date      date
submission_date    date            -- due date
total_marks        decimal/int     -- rename from `marks`; it is the maximum
description         text
attachment         json            -- list of file paths (keep array cast)
evaluation_date    date null       -- stamped when evaluated
evaluated_by       FK users null
academic_year_id   FK academic_years   -- KEEP scoping
active_status      bool
created_by/updated_by, timestamps
```

Changes vs reference:
- **Drop `school_id`** and the global school scope entirely (single school).
- Add an explicit **`teacher_id`** (staff) instead of inferring the teacher from
  `created_by`. Keep `created_by`/`evaluated_by` as user audit columns.
- Rename `marks` → `total_marks`, cast numeric.
- **Keep `academic_year_id` scoping** (equivalent to `academic_id`).
- Drop `course_id`/`lesson_id`/`chapter_id`/`student_ids`/`record_id`/`shift_id`
  from core.
- Keep "one homework row per section" if you want section-level control, OR allow a
  section multi-select in the UI that fans out to multiple rows on save (as the
  reference does). Recommended: one row per (class, section, subject, homework).

### 4.2 `homework_students` (per-student evaluation)

```
id                 PK
homework_id        FK homeworks (cascade on delete)
student_id         FK students
complete_status    enum('complete','incomplete')   -- replace magic 'C'/'I'
obtained_marks     decimal/int null
teacher_comment    text null
evaluated_at       timestamp        -- explicit per-student evaluation time
evaluated_by       FK users
academic_year_id   FK academic_years
timestamps
UNIQUE (homework_id, student_id)     -- enforce the pair in DB
```

Changes vs reference:
- **Drop `school_id`.**
- Add a **DB unique constraint on `(homework_id, student_id)`** and use upsert instead
  of delete-then-insert.
- Replace magic `'C'` string with a real enum/boolean; map `'C'` → complete on migration.
- Add explicit `evaluated_at`/`evaluated_by` per row (reference relied on `created_at`).

### 4.3 Roster rule (unchanged, and important)

- **Do NOT snapshot the class roster when the homework is created.** Generate the
  student list at evaluation and report time from **live enrollment** in the target
  class+section for the homework's academic year. `homework_students` rows exist only
  for students the teacher has actually evaluated.
- Completion % = `count(complete rows) / count(currently enrolled students)`.

### 4.4 Optional: `homework_submissions`

If student-side uploads are in scope, mirror `sm_upload_homework_contents` as
`homework_submissions (homework_id, student_id, description, files json, submitted_at)` —
one row per student upload, drop `school_id`, keep `academic_year_id`.

---

## 5. File / Symbol Reference (reference system)

- Header model: `c:\infixedu\app\SmHomework.php` (table `sm_homeworks`, global
  academic+school scope, `file` cast to array, `getHomeworkPercentage`,
  `homeworkCompleted` = rows with `complete_status = 'C'`).
- Per-student model: `c:\infixedu\app\SmHomeworkStudent.php` (table
  `sm_homework_students`; relations to student, homework, evaluating user).
- Submission model: `c:\infixedu\app\SmUploadHomeworkContent.php`
  (table `sm_upload_homework_contents`).
- Stub (ignore): `c:\infixedu\app\SmStudentHomework.php`.
- Controller: `c:\infixedu\app\Http\Controllers\Admin\Homework\SmHomeworkController.php`
  — `saveHomeworkData` (assign), `evaluationHomework` + `saveHomeworkEvaluationData`
  (evaluate), `searchEvaluation` / `viewEvaluationReport` / `homeworkReportSearch`
  (reporting).
- Schema: `c:\infixedu\andiedu (1).sql` — `sm_homeworks` (line ~12193),
  `sm_homework_students` (~12225), `sm_upload_homework_contents` (~17884).
```
