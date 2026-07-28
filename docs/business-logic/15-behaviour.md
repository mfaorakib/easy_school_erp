# 15 — Behaviour Records (Student Conduct / Incidents)

Documents the BEHAVIOUR RECORDS business logic of the reference system so it can be
faithfully rebuilt for a single-school ERP. Source module: `Modules/BehaviourRecords`.

The reference system models behaviour as **Incidents** (reusable behaviour-type
definitions, each carrying a signed point value) that are **assigned** to individual
students, producing per-student behaviour records that snapshot the point at
assignment time. Positive points = rewards, negative points = penalties.

---

## 1. Entities & Tables

### 1.1 `incidents` — Behaviour Type / Incident Definition
The catalogue of reusable behaviour types (e.g. "Late to class", "Helped a peer").

| Column        | Type            | Notes |
|---------------|-----------------|-------|
| `id`          | bigint PK       | |
| `title`       | varchar(191)    | Name of the behaviour/incident. Required. |
| `point`       | double, nullable | **Signed** point value. Positive = reward, negative = penalty. Required on input. |
| `description` | text, nullable  | Free-text detail. |
| `school_id`   | int default 1   | Multi-school tenant key. **DROP in rebuild.** |
| `created_at` / `updated_at` | timestamps | |

- **NOT academic-year scoped** — incident definitions are global to the school and
  persist across years.
- Negative points are produced at save time: the create/update form posts a plain
  positive `point` plus a `negativePoint` (create) / `editNegativePoint` (update) flag;
  the controller stores `negativePoint == 1 ? -point : point`. So the sign is baked
  into the stored `point`.

### 1.2 `assign_incidents` — Per-Student Behaviour Record
One row per (student × incident) assignment event.

| Column        | Type              | Notes |
|---------------|-------------------|-------|
| `id`          | bigint PK         | |
| `point`       | int, nullable     | **Snapshot** of `incidents.point` at assignment time (copied, not referenced). |
| `incident_id` | int, unsigned     | FK → `incidents.id` (the behaviour type). |
| `record_id`   | int, unsigned     | FK → `student_records.id` (the student's class/section/year enrolment record). |
| `student_id`  | int, nullable     | FK → `sm_students.id` (denormalised for fast per-student aggregation). |
| `added_by`    | int, unsigned     | FK → `users.id` — teacher/admin who recorded it. |
| `academic_id` | int, nullable     | FK → academic year. **Academic-year scoped** (set from `getAcademicId()`). |
| `school_id`   | int default 1     | Tenant key. **DROP in rebuild.** |
| `created_at` / `updated_at` | timestamps | `created_at` is the effective incident date. |

- **Academic-year scoped** via `academic_id` (and via `record_id` → StudentRecord,
  which is itself year/class/section scoped).
- No separate "date" column — the record's date is `created_at`.

### 1.3 `assign_incident_comments` — Comment Thread on a Record
Discussion thread attached to a single assigned incident (visible/writable by
student/parent depending on settings).

| Column        | Type            | Notes |
|---------------|-----------------|-------|
| `id`          | bigint PK       | |
| `user_id`     | int, nullable   | FK → `users.id` (comment author). |
| `comment`     | longtext        | |
| `incident_id` | int, unsigned   | FK → `assign_incidents.id` (NOT the type — the assigned record). |
| `school_id`   | int default 1   | **DROP in rebuild.** |
| timestamps    | | |

### 1.4 `behaviour_record_settings` — Module Settings (single row, id = 1)
Visibility / participation toggles (0 or 1 each):

| Column           | Meaning |
|------------------|---------|
| `student_comment`| Can students comment on their records. |
| `parent_comment` | Can parents comment. |
| `student_view`   | Can students view their behaviour records. |
| `parent_view`    | Can parents view. |
| `school_id`      | **DROP in rebuild.** |

A seeded row (all zeros) is inserted on migration. Also seeds SMS + email templates
for a `behaviour_record_update` notification with variables
`[student_name] [class] [section] [assigned_ny] [school_name] [incident] [point]
[parent_name] [total_point] [role]`.

### 1.5 Relationships
- `Incident hasMany AssignIncident` (via `incident_id`).
- `AssignIncident belongsTo Incident`, `belongsTo User (added_by)`,
  `belongsTo SmAcademicYear (academic_id)`, `belongsTo StudentRecord (record_id)`.
- `SmStudent hasMany AssignIncident (student_id)` — used for per-student totals.
- `StudentRecord hasMany AssignIncident (record_id)`.
- `AssignIncidentComment belongsTo AssignIncident (incident_id)` and `belongsTo User`.

---

## 2. Point Model (positive / negative)

- **Point lives on the type** (`incidents.point`) and is **snapshotted onto the record**
  (`assign_incidents.point = incident.point`) at assignment time. Editing a type's point
  later does NOT retroactively change existing records.
- **Sign convention:** a single signed `point` field. Positive value = reward/merit;
  negative value = penalty/demerit. There is no separate "category" or "is_negative"
  column stored — the negativity is encoded directly in the sign of `point`, derived
  from the `negativePoint` form flag at create/update time.
- There is no enumerated "category" field beyond `title`/`description`; behaviour type
  IS the category.

### Aggregation
- **Per-student total** is computed on the fly, never stored:
  `SmStudent::withSum('incidents', 'point')` → `incidents_sum_point`
  (simple signed sum, so rewards and penalties net against each other).
- A secondary accessor `getIncidentsSumPointAttribute()` returns
  `incidents_sum_point_1 - incidents_sum_point_2` — a legacy/reporting split that
  treats `incident_id = 1` as the positive bucket and `incident_id = 2` as the negative
  bucket. This is brittle (hard-coded ids) and should NOT be reproduced; use the plain
  signed sum instead.
- **Per-class/section total:** `SmClass::withSum('allIncident', 'point')`, ordered
  descending for ranking.

---

## 3. Assign / Record Flow

Recording happens from the **Assign Incident** screen (admin/teacher):

1. **Search step** (`assignIncidentSearch` / `assignIncidentDatatable`): filter students
   by academic year (required for non-university), class, section, shift, name, roll no.
   The datatable lists matching active students with their incident count
   (`withCount('incidents')`) and running signed total (`withSum('incidents','point')`).
2. **Assign step** (`assignIncidentSave`): for the chosen student the form posts
   `student_id`, `record_id`, and an array `incident_ids[]` (one or many behaviour types
   selected at once). For each incident id the controller:
   - loads the `Incident`,
   - creates an `AssignIncident` with:
     `point = incident.point` (snapshot),
     `incident_id`, `student_id`, `record_id`,
     `added_by = Auth::user()->id`,
     `academic_id = getAcademicId()`.
   - `school_id` defaults to 1.
   - No comment/description is stored on the record itself at creation (that comes via
     the comment thread).
3. **Granularity:** the record targets **one student at a time**, but **multiple
   incident types can be applied in a single submit** (the loop over `incident_ids`).
   There is no true "bulk to a whole class/section" write — bulk selection is per-student
   across the searched list.
4. **Delete** (`assignIncidentDelete`): removes a single `AssignIncident` row.
5. **Comments** (`BehaviourCommentController`): `incidentCommentSave` appends an
   `AssignIncidentComment` (author = auth user) to an assigned record; list/view gated by
   `behaviour_record_settings`.

A notification (SMS/email template `behaviour_record_update`) is intended on assignment,
carrying the incident, its point, and the student's new `total_point`.

---

## 4. Reports

1. **Student Incident Report** (`studentIncidentReportSearch`): filter by academic year +
   class + section (all required; shift optional). Per student shows incident count and
   positive/negative point sums. NOTE the reference computes positive as
   `SUM(point) where incident_id = 1` and negative as `where incident_id = 2` — again a
   hard-coded-id hack; the correct rebuild is `SUM(point) where point > 0` vs `< 0`.
   Drill-down modal lists all of a student's incidents (type, author, year, date).
2. **Student Behaviour Rank Report** (`studentBehaviourRankReportSearch`): filter by year
   (+ class/section/shift), then threshold on total points — `type` = `greater_than_or_equal`
   or `lesser_than_or_equal` against a `point` value, implemented as
   `groupBy(student_id) havingRaw('SUM(point) >= ?' / '<= ?')`. Produces a ranked list of
   students meeting the merit/demerit threshold, each with `withSum('incidents','point')`.
3. **Class/Section-wise Rank Report** (`classSectionWiseRankReport`): classes ordered by
   `SUM(point)` descending, with per-class student drill-down.
4. **Incident-wise Report** (`incidentWiseReport`): for each behaviour type, list all
   students it was assigned to.

---

## 5. Clean Single-School Rebuild Recommendation

Drop all multi-tenant columns (`school_id`) and the hard-coded `incident_id = 1/2`
bucketing. Keep the snapshot-point design and academic-year scoping on records.

### `behaviour_types` (was `incidents`)
```sql
CREATE TABLE behaviour_types (
    id           BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    title        VARCHAR(191) NOT NULL,          -- behaviour/incident name
    point        DOUBLE NOT NULL,                -- SIGNED: >0 reward, <0 penalty
    description  TEXT NULL,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    created_at   TIMESTAMP NULL,
    updated_at   TIMESTAMP NULL
);
```
- Store the signed `point` directly (derive sign from a UI "penalty" toggle, as the
  reference does). Optionally add an explicit `category` enum(`reward`,`penalty`) that is
  kept consistent with the sign, to make reporting clean without id hacks.
- NOT academic-year scoped (definitions persist across years).

### `behaviour_records` (was `assign_incidents`)
```sql
CREATE TABLE behaviour_records (
    id                BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    behaviour_type_id BIGINT UNSIGNED NOT NULL,  -- FK behaviour_types
    student_id        BIGINT UNSIGNED NOT NULL,  -- FK students (denormalised)
    student_record_id BIGINT UNSIGNED NOT NULL,  -- FK enrolment record (class/section/year)
    academic_year_id  BIGINT UNSIGNED NOT NULL,  -- academic-year scoped
    point             DOUBLE NOT NULL,           -- SNAPSHOT of behaviour_types.point
    incident_date     DATE NOT NULL,             -- explicit date (was created_at)
    comment           TEXT NULL,                 -- optional note at record time
    recorded_by       BIGINT UNSIGNED NOT NULL,  -- FK users (teacher/admin)
    created_at        TIMESTAMP NULL,
    updated_at        TIMESTAMP NULL,
    INDEX (student_id), INDEX (academic_year_id), INDEX (behaviour_type_id)
);
```
- **Snapshot `point`** from the type at creation so later edits to the type don't rewrite
  history — matches reference behaviour.
- **Academic-year scoped** via `academic_year_id`.
- Add an explicit `incident_date` rather than relying on `created_at`.

### `behaviour_record_comments` (was `assign_incident_comments`)
```sql
CREATE TABLE behaviour_record_comments (
    id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    behaviour_record_id BIGINT UNSIGNED NOT NULL,  -- FK behaviour_records
    user_id             BIGINT UNSIGNED NOT NULL,
    comment             LONGTEXT NOT NULL,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL
);
```

### `behaviour_settings` (was `behaviour_record_settings`, single row)
`student_comment`, `parent_comment`, `student_view`, `parent_view` booleans. No `school_id`.

### Aggregation rules (rebuild)
- Per-student total = `SUM(behaviour_records.point)` for the student (optionally filtered
  by `academic_year_id`) — a single signed sum; rewards and penalties net.
- Positive total = `SUM(point) WHERE point > 0`; negative total = `SUM(point) WHERE point < 0`
  (use the sign, never a hard-coded type id).
- Ranking = order students / classes by that signed sum, or filter with a
  `HAVING SUM(point) >= / <= threshold`.

### Record flow (rebuild)
Teacher/admin searches active students (year + class + section), selects one student, picks
one or more behaviour types, and submits. For each selected type, insert one
`behaviour_records` row snapshotting the type's point, stamping `academic_year_id`,
`student_record_id`, `incident_date`, and `recorded_by`. Optionally send a
behaviour-update notification including the student's new signed total.
