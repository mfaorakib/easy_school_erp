# Examination, Marks, Grading & Results — Business-Logic Spec (the reference system)

> Reverse-engineered from the legacy Laravel app at `c:\reference-source`. This documents *observed behavior* so it can be
> replicated in the new build. Source files/tables are cited inline. Nothing in `c:\reference-source` was modified.

This is the most intricate domain in the reference system. The data model separates the **exam term** (what a user calls
"First Term / Mid Term / Final") from the **per-subject exam setup**, from the **exam parts** (theory/practical), from the
**raw part marks**, from the **aggregated per-subject result**, and finally from the **computed merit list**. Getting these
layers right is essential for a faithful rebuild.

---

## 1. Entities & tables

Everything below is **academic-year scoped** (`academic_id`) and multi-tenant scoped (`school_id`) in the reference dump.
For the single-school rebuild we drop `school_id` but **keep `academic_id`**.

| # | Reference table | Model (`c:\reference-source\app`) | Grain / purpose |
|---|---|---|---|
| 1 | `sm_exam_types` | `SmExamType.php` | The **exam term** — the thing users select ("Mid Term"). `title`, `active_status`, `is_average`, `average_mark`, `percentage`. Academic scoped. |
| 2 | `sm_exams` | `SmExam.php` | **Exam setup header** — one row per `exam_type × class × section × subject`. Holds `exam_mark` (subject full mark) and `pass_mark`. |
| 3 | `sm_exam_setups` | `SmExamSetup.php` | **Exam parts** — one row per part of a subject exam (e.g. "Theory", "Practical", "MCQ"). `exam_id → sm_exams.id`, `exam_title`, `exam_mark` (part max), `exam_term_id` (= exam_type_id), class/section/subject. |
| 4 | `sm_exam_schedules` | `SmExamSchedule.php` | **Exam routine header** — per `exam_id × class × section`. `date`, `exam_period_id`, `room_id`, `start_time`, `end_time`, `teacher_id`. |
| 5 | `sm_exam_schedule_subjects` | `SmExamScheduleSubject.php` | **Routine rows per subject** — `exam_schedule_id`, `subject_id`, `date`, `start_time`, `end_time`, `room`, `full_mark`, `pass_mark`. |
| 6 | `sm_marks_grades` | `SmMarksGrade.php` | **Grade scale** — `grade_name`, `gpa`, `from`/`up` (GPA band), `percent_from`/`percent_upto` (percentage band), `description`. |
| 7 | `sm_mark_stores` | `SmMarkStore.php` | **Raw part marks** — one row per `student × exam_type × class × section × subject × exam_setup_id (part)`. `total_marks` (that part's obtained mark), `is_absent`, `teacher_remarks`. |
| 8 | `sm_result_stores` | `SmResultStore.php` | **Per-subject aggregated result** — one row per `student × exam_type × class × section × subject`. `total_marks` (sum of parts), `total_gpa_point`, `total_gpa_grade`, `is_absent`. |
| 9 | `sm_temporary_meritlists` | `SmTemporaryMeritlist.php` | **Computed merit list** — one row per `student × exam × class × section`. `total_marks`, `average_mark`, `gpa_point`, `result` (overall grade), `merit_order` (rank), `roll_no`, `marks_string`, `subjects_string`. |
| 10 | `custom_result_settings` | `CustomResultSetting.php` | Result config — `exam_type_id`, `exam_percentage` (term weight), **`merit_list_setting`** (`total_grade` / `total_mark` / roll), print styling. |
| 11 | `sm_exam_settings` | `SmExamSetting.php` | Exam-publish gating — `exam_type`, `title`, `publish_date`, `start_date`, `end_date`, `file` (signature/marksheet header). |
| 12 | `sm_exam_attendances` + `sm_exam_attendance_children` | `SmExamAttendance` / `SmExamAttendanceChild` | Exam attendance (P/A) per subject; **must be taken before marks entry** (unless step is skipped). Child `attendance_type` = `P`/`A`. |
| — | `sm_marks_registers` + `sm_marks_register_children`, `sm_exam_marks_registers` | `SmMarksRegister`, `SmMarksRegisterChild`, `SmExamMarksRegister` | **Legacy / secondary** structures. The live mark-entry flow writes `sm_mark_stores` + `sm_result_stores`, not these. `SmMarksRegister` survives only as a helper class (`highestMark`, `is_absent_check`, `subjectDetails`). Safe to drop in rebuild. |

### 1.1 The layer relationship (critical mental model)

```
SmExamType (term: "Mid Term")
   └── SmExam           (setup: term+class+section+subject → subject full_mark, pass_mark)
          └── SmExamSetup   (parts: "Theory"=70, "Practical"=30 → part exam_mark)   [sum of parts = subject full mark]

Marks entry writes, per student:
   SmMarkStore   (one row PER PART)  ── summed ──►  SmResultStore (one row PER SUBJECT: total_marks, gpa, grade)

Result generation reads SmResultStore across subjects  ──►  SmTemporaryMeritlist (per student: totals, avg GPA, rank)
```

### 1.2 Academic-year / global-scope notes

- Global scopes in the reference: `SmExamType` and `SmExamSetting` use `StatusAcademicSchoolScope`+`GlobalAcademicScope`;
  `SmExam` and `SmMarkStore` and `SmExamSchedule` use `AcademicSchoolScope`; `SmMarksGrade` uses `ActiveStatusSchoolScope`.
- `SmExamSetup`, `SmResultStore`, `SmTemporaryMeritlist`, `SmExamScheduleSubject` have **no** global scope — every query
  filters `academic_id` + `school_id` manually. In the rebuild, apply a single academic-year global scope uniformly.

---

## 2. Marks entry flow

Controllers: `SmExamMarkRegisterController.php` (`store`) and `SmExaminationController.php`
(`marksRegisterSearch`, `marksRegisterStore`). Both write the same two tables identically.

### 2.1 Prerequisites (setup)

1. **Exam term** created in `sm_exam_types` (`SmExaminationController::exam_type_store`).
2. **Exam setup** created in `SmExamController::store`: for each `class × section × subject` under the term it inserts:
   - one `sm_exams` row with `exam_mark` = **subject full mark** and `pass_mark` = subject pass mark;
   - one `sm_exam_setups` row **per part** from the `exam_title[]` / `exam_mark[]` arrays (e.g. Theory 70 + Practical 30).
     The part marks are expected to sum to the subject full mark.
3. **Exam schedule** (`SmExaminationController::examScheduleStore`): writes `sm_exam_schedules` + child
   `sm_exam_schedule_subjects` rows carrying per-subject `date`, `start_time`, `end_time`, `room`, `full_mark`, `pass_mark`.
4. **Exam attendance** taken per subject. Marks entry **hard-blocks** if attendance is missing, unless
   `isSkip('exam_attendance')` is set (`ExamStepSkip` table / `CustomResultSettingController::isSkip`).

### 2.2 Entering marks

Teacher/admin picks **exam term + class + section + subject** (section optional → all sections). The form renders
**one numeric input per exam part** (`SmExamSetup` rows → `number_of_exam_parts`), plus an **Absent** checkbox and an
optional teacher remark per student. Students come from `StudentRecord`/`SmStudent` (`active_status=1`, `is_promote=0`),
sorted by roll number.

### 2.3 Persistence algorithm (`store` / `marksRegisterStore`)

For each student, if any part marks were submitted:

```
total_marks_persubject = 0
for each part_mark in marks[student]:                 # iterate exam parts (SmExamSetup)
    mark_by_exam_part = part_mark ?? 0                 # blank part → 0
    total_marks_persubject += mark_by_exam_part
    exam_setup_id = exam_Sids[student][part_index]
    # (non-university path DELETEs any existing SmMarkStore for this key first, then re-inserts)
    upsert SmMarkStore(student, exam_term, class, section, subject, exam_setup_id):
        total_marks     = mark_by_exam_part            # PART mark, not subject total
        is_absent       = student in absent_students ? 1 : 0
        teacher_remarks = ...
# after all parts:
subject_full_mark  = subjectFullMark(exam_term, subject, class, section, shift)   # = SmExam.exam_mark
mark_by_percentage = subjectPercentageMark(total_marks_persubject, subject_full_mark)   # round(obtained/full*100, 2)
mark_grade = SmMarksGrade where percent_from <= mark_by_percentage <= percent_upto
upsert SmResultStore(student, exam_term, class, section, subject):
    total_marks     = total_marks_persubject           # SUBJECT total (sum of parts)
    total_gpa_point = mark_grade.gpa
    total_gpa_grade = mark_grade.grade_name
    is_absent       = student in absent_students ? 1 : 0
```

Key facts:
- **Theory/practical split** = the `SmExamSetup` parts. Each part mark is stored separately in `sm_mark_stores`
  (`exam_setup_id`); the **subject total** is their sum and is stored once per subject in `sm_result_stores`.
- **Absent handling**: a student in the `absent_students[]` array gets `is_absent = 1` on *every* part row and on the
  subject result row. Absent does not stop the grade being computed from whatever marks were entered (typically 0).
- The whole store runs in a DB transaction; failure rolls back.
- Helpers (`c:\reference-source\app\Helpers\Helper.php`):
  - `subjectFullMark($examtype,$subject,$class,$section,$shift)` → `SmExam.exam_mark` (rounded 2dp; 0 if missing).
  - `subjectPercentageMark($obtained,$full)` → `round($obtained/$full*100, 2)`; returns 0 if full mark is 0/null.

---

## 3. Grade computation

Grade scale = `sm_marks_grades`, seeded (default, per academic year) as:

| grade_name | gpa | GPA band (`from`–`up`) | percentage band (`percent_from`–`percent_upto`) | description |
|---|---|---|---|---|
| A+ | 5.0 | 5.00–5.99 | 80–100 | Outstanding |
| A  | 4.0 | 4.00–4.99 | 70–79.99 | Very Good |
| A- | 3.5 | 3.50–3.99 | 60–69.99 | Good |
| B  | 3.0 | 3.00–3.49 | 50–59.99 | (—) |
| C  | 2.0 | 2.00–2.99 | 40–49.99 | Bad |
| D  | 1.0 | 1.00–1.99 | 33–39.99 | Very Bad |
| F  | 0.0 | 0.00–0.99 | 0–32.99 | Failed |

Two **independent lookup axes** on the same table:

1. **Percentage → grade/GPA** (used at subject level, mark-entry time):
   `SmMarksGrade where percent_from <= pct <= percent_upto`. Helpers: `getGrade($marks)` (returns `grade_name`),
   `markGpa($marks)` (returns full row; falls back to the min-gpa/fail grade if no band matches).
2. **GPA → grade name** (used to label an *averaged* overall GPA): `SmMarksGrade where from <= gpa <= up`.
   Helper: `getGrade($grade)` / `CustomResultSetting::gpaToGrade($gpa)`.

**Pass mark / fail grade:**
- Per-subject pass marks live in `sm_exams.pass_mark` and `sm_exam_schedule_subjects.pass_mark` (and a class-level
  `sm_classes.pass_mark`). The reference does *not* use these numeric pass marks in the merit-list fail test — instead it
  treats the **lowest-GPA grade** (min `gpa`, i.e. `F` / gpa 0) as the "fail grade" and fails a subject when its computed
  grade equals that fail grade.
- Absent (`is_absent=1`) forces the subject/overall result to `F`.

---

## 4. Result computation & merit list (the core algorithm)

Source: `SmExaminationController::meritListReportSearch` (primary, richer) and `::make_merit_list` (simpler variant).
Reads `sm_result_stores`, writes `sm_temporary_meritlists`.

For **each student** in the class/section for the chosen exam term:

### 4.1 Per-subject gather
Iterate the class/section's *eligible* (exam-assigned) subjects. For each, require a `SmResultStore` row (else it errors
"Please register marks for all students & all subjects"). Build `marks_string` (comma-joined per-subject marks) — and when
`generalSetting()->result_type == 'mark'` each subject mark is first converted to a percentage
(`obtained * 100 / subject_full_mark`) for display; when `result_type == 'grade'` the raw mark is shown.

### 4.2 Aggregate across subjects
```
results         = SmResultStore rows for (exam_type, class, section, student)
total_marks     = Σ SmResultStore.total_marks
total_gpa_point = Σ SmResultStore.total_gpa_point
n               = results.count()                       # number of subjects
average_mark    = total_marks == 0 ? 0 : floor(total_marks / n)
total_GPA       = total_gpa_point == 0 ? 0 : total_gpa_point / n     # simple average of subject GPAs
student_gpa_point = number_format(total_GPA, 2)
is_absent       = (any subject result with is_absent=1) ? 1 : 0
```

### 4.3 Pass / fail rule (fail if any non-optional subject fails)
```
dat = []
for each subject result:
    if subject is NOT an optional subject (not in sm_optional_subject_assigns):
        if markGpa(...).grade_name == fail_grade_name:      # fail_grade = grade with MIN gpa (F)
            dat.push(fail_gpa)
gpa_point = dat not empty ? dat[0] (= fail gpa) : student_gpa_point
```
So a student who fails **any non-optional subject** has their overall `gpa_point` overridden to the **fail GPA**, which
makes the overall grade come out as **F**. Optional subjects are **excluded** from this fail test (but still counted in the
GPA average denominator `n`). Absent students are likewise labelled `F`.

> Note: in the reference code the fail check calls `markGpa($subject_total_mark)` rather than the student's obtained mark —
> a known quirk of the source. The *intent* to replicate is: **overall result is FAIL if any required subject's grade is the
> fail grade.** In the rebuild, evaluate the fail test against the student's obtained per-subject percentage/grade.

### 4.4 Overall grade & write
```
overall_grade = is_absent ? 'F' : getGrade(gpa_point).grade_name      # GPA→grade band (from/up)
upsert SmTemporaryMeritlist(student, exam, class, section):
    total_marks   = total_marks
    average_mark  = average_mark
    gpa_point     = gpa_point
    result        = overall_grade
    marks_string  = per-subject marks
    merit_order   = student_gpa_point      # provisional; re-ranked below
    roll_no       = student roll
```

### 4.5 Rank / position ordering — driven by `CustomResultSetting.merit_list_setting`
After all students are stored, the list is ordered per the setting:

| `merit_list_setting` | Ordering | Meaning |
|---|---|---|
| `total_grade` | `merit_order` (= avg GPA) **DESC** | Rank by GPA (highest first) |
| `total_mark`  | `total_marks` **DESC** | Rank by total marks (highest first) |
| anything else | `roll_no` **ASC** | List by roll number (no ranking) |

In `make_merit_list` the simpler path orders by `gpa_point DESC` and writes a sequential `merit_order = 1,2,3…` — i.e. the
**position/rank** is the row index in the sorted list. Default seeded `merit_list_setting` is **`total_mark`**.

### 4.6 Term-combined result (multi-term weighting) — optional feature
`CustomResultSetting` supports combining several exam terms into a final result:
- Each term has `exam_percentage` (mirrored into `SmExamType.percentage`) as a weight.
- `CustomResultSetting::termResult()` = average of subject GPAs for a term (`Σ subject_gpa / subject_count`).
- `getFinalResult()` = `(percentage / 100) * term_gpa` per term; terms are summed for a weighted final GPA.
- `SmExamType.is_average` / `average_mark` support averaging terms instead of weighting. This is a secondary feature; a basic
  rebuild can ship single-term results first and add weighting later.

---

## 5. Settings that affect results

- **`generalSetting()->result_type`** (`mark` vs `grade`): controls whether the merit list / mark sheet displays each
  subject as a **percentage of full mark** (`mark`) or as the **raw obtained mark / grade** (`grade`). It changes
  presentation of `marks_string`, not the stored GPA.
- **`custom_result_settings.merit_list_setting`**: `total_grade` | `total_mark` | (roll) — the ranking key for the merit
  list (see §4.5).
- **`custom_result_settings.exam_percentage`** + **`sm_exam_types.percentage` / `is_average` / `average_mark`**: term
  weighting for combined final results (§4.6).
- **`sm_exam_settings`** (`publish_date`, `start_date`, `end_date`, `active_status`, `file`): gates result/marksheet
  publication and supplies the marksheet header/signature.
- **`ExamStepSkip`** (`isSkip('exam_schedule'|'exam_attendance')`): lets a school bypass the schedule/attendance
  prerequisites before marks entry.
- **Optional subjects** (`sm_class_optional_subject`, `sm_optional_subject_assigns`): excluded from the fail test (§4.3).

---

## 6. Clean single-school rebuild recommendation

Drop `school_id` everywhere; **keep `academic_year_id`** (rename `academic_id`) on every table below and scope all queries
by the active year. Collapse the reference's overlapping legacy tables (`sm_marks_registers*`, `sm_exam_marks_registers`)
— they are unused by the live flow. Suggested schema:

```
exam_terms            (id, academic_year_id, title, is_average, average_mark, weight_percentage, active, ...)
                        -- was sm_exam_types

exams                 (id, academic_year_id, exam_term_id, class_id, section_id, subject_id,
                        full_mark, pass_mark, active)                       -- was sm_exams (per-subject setup)

exam_parts            (id, exam_id, title, max_mark)                        -- was sm_exam_setups (theory/practical)

exam_schedules        (id, academic_year_id, exam_term_id, class_id, section_id, subject_id,
                        exam_date, start_time, end_time, room_id, teacher_id, full_mark, pass_mark)
                        -- merges sm_exam_schedules + sm_exam_schedule_subjects

grade_scales          (id, academic_year_id, grade_name, gpa,
                        gpa_from, gpa_to, percent_from, percent_to, description, active)   -- was sm_marks_grades

marks                 (id, academic_year_id, exam_term_id, exam_id, exam_part_id,
                        student_id, class_id, section_id, subject_id,
                        obtained_mark, is_absent, teacher_remark)           -- was sm_mark_stores (one row per PART)

subject_results       (id, academic_year_id, exam_term_id, student_id, class_id, section_id, subject_id,
                        total_marks, gpa_point, grade_name, is_absent, teacher_remark)     -- was sm_result_stores

results / merit_list  (id, academic_year_id, exam_term_id, student_id, class_id, section_id,
                        total_marks, average_mark, gpa_point, overall_grade,
                        is_absent, is_fail, rank_position)                  -- was sm_temporary_meritlists (make permanent)

result_settings       (id, academic_year_id, exam_term_id, weight_percentage,
                        merit_list_setting, result_display_type)            -- was custom_result_settings + generalSetting.result_type

exam_publish_settings (id, academic_year_id, exam_term_id, title, publish_date, start_date, end_date, active)
                        -- was sm_exam_settings
```

**Behavioural rules to preserve exactly:**
1. Marks entered per **exam part**; **subject total = Σ parts**; percentage = `total/full*100`.
2. Subject grade & GPA come from the **percentage band** of `grade_scales`.
3. Overall student GPA = **simple average of subject GPAs** (`Σ gpa / subject_count`); overall grade from the **GPA band**.
4. **Absent** ⇒ that subject/overall result = fail grade `F`.
5. **Pass/Fail**: overall = FAIL if **any non-optional subject** is at the fail grade; optional subjects excluded from the
   test but still counted in the GPA-average denominator.
6. **Rank** by `merit_list_setting`: GPA desc (`total_grade`), total-marks desc (`total_mark`), or roll asc — position =
   ordered index.
7. `result_display_type` (`mark`/`grade`) toggles per-subject percentage vs raw-mark display only.
8. Recommended cleanups: make the merit list a **permanent** table keyed by term (not the reference's `iid`/time() temp
   rows); store `is_fail` and `rank_position` explicitly instead of recomputing; evaluate the fail test on the student's
   obtained mark (fixing the `markGpa($subject_total_mark)` quirk).
```
