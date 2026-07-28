# Attendance — Business Logic (from the reference system)

This document captures how attendance works in the existing Laravel school-management application ("the reference system"), so the behaviour can be faithfully rebuilt in the new app. All findings are extracted read-only from the reference source.

There are three independent attendance flows:

1. **Daily student attendance** — one record per student per day.
2. **Subject-wise student attendance** — one record per student per subject per day.
3. **Staff attendance** — one record per staff per day.

An exam-attendance feature also exists but is out of scope here.

---

## 1. Status codes (the single most important detail)

All three attendance tables store a single-letter code in a `VARCHAR(10)` column. The column comment in every migration is identical:

> `Present: P  Late: L  Absent: A  Holiday: H  Half Day: F`

| Meaning   | Stored code |
|-----------|-------------|
| Present   | `P` |
| Late      | `L` |
| Absent    | `A` |
| Holiday   | `H` |
| Half Day  | `F` |

Notes on the codes:

- **Half Day is `F`, not `H`.** This is a common trap — `H` is Holiday. In the daily-attendance UI the fourth radio button is even labelled/ID'd "H" but its `value="F"` (`resources/views/backEnd/studentInformation/student_attendance.blade.php`: radios emit `value="P"`, `value="L"`, `value="A"`, `value="F"`).
- `H` (Holiday) is never a manual per-student radio choice. It is written only by the dedicated "mark holiday" flow, or when a `mark_holiday` flag is present on the daily-store request.
- Codes are free-form strings (no DB enum / no check constraint), so integrity depends entirely on application code.

---

## 2. Entities / tables

### 2.1 `sm_student_attendances` (daily) — model `App\SmStudentAttendance`
Migration `2014_12_01_000055_create_sm_student_attendances_table.php`.

| Column | Type | Notes |
|--------|------|-------|
| `id` | increments | |
| `attendance_type` | string(10) | one of P/L/A/H/F |
| `notes` | string(500) | nullable |
| `attendance_date` | date | |
| `student_id` | int FK → `sm_students.id` | |
| `record_id` | int | legacy, unused |
| `student_record_id` | bigint | FK to `student_records.id` (FK not enforced in this table) |
| `class_id` | int FK → `sm_classes.id` | |
| `section_id` | int FK → `sm_sections.id` | |
| `shift_id` | int | added when the "shift" feature is enabled |
| `school_id` | int FK → `sm_schools.id` | multi-tenant |
| `academic_id` | int FK → `sm_academic_years.id` | academic year/session |
| plus University module cols | | `un_academic_id`, `un_session_id`, `un_department_id`, `un_faculty_id`, `un_semester_id`, `un_semester_label_id`, `un_section_id` |
| `created_by`, `updated_by`, `active_status`, timestamps | | |

The model applies a global `AcademicSchoolScope` (auto-filters by `school_id` + `academic_id`). It has a `studentInfo()` belongsTo and a `scopeMonthAttendances($month)` using `whereMonth('attendance_date', ...)`.

**There is no unique constraint** on `(student_id, attendance_date, ...)`. Uniqueness is enforced only by application logic (delete-then-insert, see §4).

### 2.2 `sm_subject_attendances` (subject-wise) — model `App\SmSubjectAttendance`
Migration `2020_01_29_110503_create_sm_subject_attendances_table.php`. Same shape as the daily table **plus**:

| Column | Type | Notes |
|--------|------|-------|
| `subject_id` | int FK → `sm_subjects.id` | the subject the attendance is for |
| `student_record_id` | bigint FK → `student_records.id` | FK **is** enforced here |
| `notify` | boolean default false | used by `getAbsentSubjectList()` to batch absence notifications |

Again no DB unique constraint; enforced in code.

### 2.3 `sm_staff_attendences` (staff) — model `App\SmStaffAttendence`
Migration `2014_12_01_000057_create_sm_staff_attendences_table.php`. Note the different column spelling — **`attendence_type`** and **`attendence_date`** (British-ish misspelling used consistently for staff only).

| Column | Type | Notes |
|--------|------|-------|
| `id` | increments | |
| `attendence_type` | string(10) | P/L/A/H/F |
| `notes` | string(500) | |
| `attendence_date` | date | |
| `staff_id` | int FK → `sm_staffs.id` | |
| `school_id` | int FK | |
| `academic_id` | int FK | |
| `created_by`, `updated_by`, timestamps | | |

No `class`/`section` — staff attendance is by HR role, not class. No global scope on this model.

### 2.4 Import staging tables
- `student_attendance_bulks` (`App\StudentAttendanceBulk`) and `sm_student_attendance_imports` — staging for Excel bulk import of student attendance.
- `sm_staff_attendance_imports` (`App\SmStaffAttendanceImport`, fillable: `attendence_date, in_time, out_time, attendance_type, notes, staff_id`) — staging for staff bulk import.

These are scratch tables: rows are inserted by the Excel importer, copied into the real attendance tables, then deleted.

---

## 3. Roster: where the list of students comes from

The roster is **always derived live from `student_records`** (model `App\Models\StudentRecord`), never from a stored class list. `student_records` is the per-academic-year enrollment record linking a student to a class/section/shift/academic year.

Daily search (`SmStudentAttendanceController::studentSearch`) builds the roster:

```
StudentRecord::with('studentDetail', 'studentDetail.DateWiseAttendances')
  ->where('class_id', $class_id)
  ->whereHas('studentDetail', fn($q) => $q->where('active_status', 1))  // only active students
  ->where('section_id', $section_id)
  ->when($shift, ... where('shift_id', $shift))
  ->where('academic_id', getAcademicId())
  ->where('school_id', Auth::user()->school_id)
  ->get()->sortBy('roll_no');
```

Key points:
- Keyed by **`student_record_id`** (the enrollment row id), not the student id, throughout store/report.
- Only **active** students are listed (`sm_students.active_status = 1`).
- Sorted by `roll_no`.
- The already-saved value for the chosen date is eager-loaded via the `DateWiseAttendances` hasOne relation on `SmStudent`, which matches on `class_id` + `section_id` + `attendance_date` (and University cols when that module is on). This pre-selects the correct radio when re-opening a date.
- Subject-wise uses the analogous `DateSubjectWiseAttendances` relation and additionally filters the roster by `SmAssignSubject` (subjects assigned to that class/section/shift).
- Teachers only see their own assigned classes (`teacherAccess()` → `$teacher_info->classes`); admins see all.

---

## 4. Take-attendance flow and storage rules

### 4.1 Daily student attendance — `studentAttendanceStore(Request)`

**Inputs:** `date`, and `attendance[<student_record_id>]` array where each element carries `student`, `class`, `section`, `shift`, `attendance_type`, `note`. Optional `mark_holiday` flag.

**Per row (`record_id => student`):**
1. **Look up existing** `SmStudentAttendance` matching `student_id` + `attendance_date` + `class_id` + `section_id` (+ `shift_id` if shifts enabled) + `student_record_id` + `academic_id` + `school_id`.
2. If found, **`->delete()`** it (hard delete-then-recreate — this is the "upsert").
3. Create a fresh row with the posted values.
4. If `mark_holiday` is set → `attendance_type = 'H'` (notes not taken); otherwise use the posted `attendance_type` and `note`.
5. Save; then fire a `Student_Attendance` notification to Student + Parent (FCM/flutter + SMS), wrapped in try/catch so notification failure never blocks the save.

So the effective uniqueness key is: **student_record_id + attendance_date (+ class/section/shift/academic/school)** → exactly one row. The pattern is *delete matching, then insert*, not an UPDATE — meaning `id` changes on every re-save and there is no DB constraint backing it.

### 4.2 Daily "mark holiday" for a whole class — `studentAttendanceHoliday(Request)`
Inputs: `class_id`, `section_id`, `shift_id`, `attendance_date`, `purpose` (`mark` / unmark). Loads the class roster from `student_records`, deletes any existing row for each student on that date, and if `purpose == 'mark'` inserts a row with `attendance_type='H'`, `notes='Holiday'`. Also sends holiday SMS + notifications to student and parent. `purpose != 'mark'` simply clears the day (unmark).

### 4.3 Subject-wise student attendance — `SmSubjectAttendanceController::storeAttendance`
Inputs: `class`, `section`, `subject`, `attendance_date`/`date`, `attendance[<student_record_id>]` (each with `student`, `class`, `section`, `attendance_type`, `note`).

Same delete-then-insert pattern, but the match key **includes `subject_id`**:
match on `student_id` + `subject_id` + `attendance_date` + `class_id` + `section_id` + `student_record_id` + `academic_id` + `school_id`, delete if present, insert new. Fires a `Subject_Wise_Attendance` notification.

- `storeAttendanceSecond` is an AJAX variant that defaults a missing `attendance_type` to `'A'` (absent) and returns JSON.
- `subjectHolidayStore` marks/unmarks `'H'` for every student in the class for one subject on one date, with notifications.

Subject-wise is therefore an **entirely separate flow and table** from daily attendance — they do not derive from or sync with each other. A school uses one or the other (there is even an `attendance_layout` general setting choosing between two subject-attendance blade layouts).

### 4.4 Staff attendance — `SmStaffAttendanceController::staffAttendanceStore`
Inputs: `date`, `id[]` (staff ids), `attendance[<staff_id>]`, `note[<staff_id>]`.

Per staff id: look up `SmStaffAttendence` by `staff_id` + `attendence_date` + `school_id`; delete if found; insert new with `attendence_type`, `notes`, `academic_id`. Then send SMS keyed on the code (`P` → present template, `A` → absent, `L` → late) and a `Staff_Attendance` notification. Roster comes from `SmStaff` filtered by HR role (`InfixRole`), excluding roles 1/2/3 (admin/student/parent), active staff only. `staffHolidayStore` writes `'H'` for a whole role on a date.

### 4.5 Bulk Excel import
- **Students** (`studentAttendanceBulkStore`): validates `attendance_date`, `file` (xlsx/csv), `class`, `section`; imports rows into `StudentAttendanceBulk`; for each class/section in the sheet deletes existing bulk + real rows for that date, then copies each staged row into `sm_student_attendances` (delete-then-insert per `student_record_id`+date). Downloadable template columns: `admission_no, class_id, section_id, attendance_date, in_time, out_time`.
- **Staff** (`staffAttendanceBulkStore`): imports into `SmStaffAttendanceImport`, deletes existing staff rows for the date, inserts present staff from the sheet, then **auto-marks every staff not in the sheet as `'A'` (absent)** for that date. Template columns: `staff_id, attendance_date, in_time, out_time`.

---

## 5. Reports (monthly grid + percentage)

Reports are all **month + year** driven and built as a per-student array the blade renders into a day-by-day grid.

- **Daily** — `SmStudentAttendanceReportController::search` / `print`: loads roster from `student_records` (class+section+shift+academic+school, sorted by roll_no); for each, pulls `SmStudentAttendance` rows where `attendance_date LIKE 'YYYY-MM%'`, selecting `attendance_type, attendance_date, student_id`. `cal_days_in_month()` gives the column count. Percentages/day-cell colouring are computed in the blade from the P/L/A/H/F codes.
- **Subject-wise** — `SmSubjectAttendanceController::subjectAttendanceReportSearch` + `subjectAttendanceAverageReportSearch` + print variants: same monthly `LIKE 'YYYY-MM%'` pattern against `sm_subject_attendances`, per `student_record_id`. Requires a subject assigned to the class/section (`SmAssignSubject`).
- **Staff** — `staffAttendanceReportSearch` / `staffAttendancePrint`: single query `whereIn('staff_id', ...)->whereYear(...)->whereMonth(...)`, `->groupBy('staff_id')` for the view; `attendence_type` codes drive the grid.

No stored aggregate/percentage column exists — every report recomputes from raw rows. Queries filter on `attendance_date LIKE 'year-month%'` (string match on the date column) or `whereMonth/whereYear`.

---

## 6. Multi-tenancy & scoping (carry over conceptually)

Every query is scoped by `school_id` (multi-tenant) and `academic_id` (academic year / session), sourced from `Auth::user()->school_id` and the `getAcademicId()` helper. `SmStudentAttendance` and `SmSubjectAttendance` enforce this via a global `AcademicSchoolScope`; staff attendance filters manually. A "shift" dimension is optional, gated by `shiftEnable()`. A University module adds an alternate hierarchy (session/faculty/department/semester) — safe to ignore for a school rebuild.

---

## 7. Clean rebuild recommendation (for the new app)

Keep the *behaviour*, fix the *data integrity*:

1. **Model statuses as a real enum**, not loose strings. Preserve the exact stored codes for data compatibility:
   `P = Present, L = Late, A = Absent, H = Holiday, F = HalfDay`.
   (Store the letters; expose a typed enum in code. Do **not** reuse `H` for half-day — half-day is `F`.)

2. **One row per student per date** for daily attendance, and **one row per student per subject per date** for subject-wise. Model these as two tables (or one table with a nullable `subject_id` and a partial/compound uniqueness that includes subject).

3. **Add a real UNIQUE constraint** the reference system lacks:
   - daily: `UNIQUE (student_record_id, attendance_date)` (scoped per tenant/academic if not already implied by the enrollment row).
   - subject-wise: `UNIQUE (student_record_id, subject_id, attendance_date)`.
   - staff: `UNIQUE (staff_id, attendance_date)`.

4. **Replace delete-then-insert with a true upsert** (`updateOrCreate` / `INSERT ... ON CONFLICT DO UPDATE`) keyed on the unique constraint. This keeps a stable primary key, avoids row churn, and is race-safe once the DB constraint exists.

5. **Roster is always live from enrollment** (`student_records` equivalent): filter by class + section + (optional) shift + academic year + tenant, active students only, ordered by roll number. Never persist a class roster snapshot for attendance.

6. **Key attendance by the enrollment record id** (`student_record_id`), not the raw student id, so a student who changes class/section mid-year keeps clean per-enrollment history. Denormalise `student_id`, `class_id`, `section_id` onto the row for fast reporting if desired, but the enrollment id is the source of truth.

7. **Holiday is a status, not a separate concept** — a class/role-wide "mark holiday" action simply upserts every rostered member to `H` for that date; "unmark" deletes those rows.

8. **Keep daily and subject-wise as independent flows.** Do not auto-derive one from the other.

9. **Notifications are side-effects** fired after a successful save and must be wrapped so failures never block or roll back the attendance write (the reference system does this via try/catch around each notification).

10. **Reports recompute from rows.** A monthly grid = `attendance_date` within a month for the roster, counted by code; percentage = `(P + optionally L/F weighting) / marked-days`. Optionally add a materialised monthly summary later for performance, but the raw rows remain authoritative.
