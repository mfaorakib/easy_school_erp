# 17 — Lesson Plan Business Logic

Reverse-engineered from the reference system (Laravel "Lesson" module). This
document describes the lesson-plan domain: how lessons/chapters, topics,
sub-topics and daily lesson planners are structured, how completion and
progress are tracked, and a clean single-school rebuild recommendation.

---

## 1. Entities & Tables

The reference system splits the domain into **two layers**:

- **Curriculum layer** — the syllabus content: lessons (chapters), their titles,
  topics, and topic titles. This is the "what should be taught" catalogue.
- **Planning layer** — the timetable-driven daily plan: a `lesson_planner` row
  per period per date that references curriculum items and holds delivery
  details (teaching method, objectives, attachments, Zoom, notes).

### 1.1 `sm_lessons` — Lesson / Chapter header

One row per lesson title, per class + section + subject. When a lesson is
created it is fanned out across every section assigned to that class+subject
(see §2), so a "lesson" is effectively section-scoped.

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| lesson_title | string, nullable | The chapter/lesson name |
| active_status | int, default 1 | Soft on/off flag |
| class_id | FK sm_classes | |
| section_id | FK sm_sections | |
| subject_id | FK sm_subjects | |
| shift_id | int, nullable | Only used when the Shift feature is enabled |
| school_id | FK sm_schools, default 1 | Multi-school tenant key |
| academic_id | FK sm_academic_years, default 1 | **Academic-year scoped** |
| user_id | int, nullable | Creator |
| timestamps | | |

Global scope: `StatusAcademicSchoolScope` (auto-filters `active_status`,
`academic_id`, `school_id`). Relations: `class`, `section`, `subject`,
`lessons()` (hasMany `SmLessonDetails`).

### 1.2 `sm_lesson_details` — legacy/unused detail mirror

A near-duplicate of `sm_lessons` (lesson_id, lesson_title, class/section/subject,
school, academic). The `SmLessonDetails` model is an empty stub and is **not
populated by any active create path** — treat it as dead/legacy. Not needed in a
rebuild.

### 1.3 `sm_lesson_topics` — Topic group (per lesson)

A grouping row that ties a set of topic titles to one lesson for a given
class+section+subject. There is one `sm_lesson_topics` row per lesson per
class/section/subject (deduplicated — see §2).

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| lesson_id | int | FK to `sm_lessons` (not DB-enforced) |
| class_id / section_id / subject_id | FK | |
| shift_id | int, nullable | |
| created_by / updated_by | int | |
| school_id | FK sm_schools | |
| academic_id | FK sm_academic_years | **Academic-year scoped** |
| active_status | int, default 1 | |
| user_id | int, nullable | |
| timestamps | | |

Global scope: `StatusAcademicSchoolScope`. Relations: `topics()` (hasMany
`SmLessonTopicDetail` on `topic_id`), `lesson()`, `class`, `section`, `subject`.

### 1.4 `sm_lesson_topic_details` — Topic (the real completion unit)

The actual topic rows. **This is where completion is tracked.**

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| lesson_id | int, nullable | FK to `sm_lessons` |
| topic_title | string, NOT NULL | The topic name |
| completed_status | string, nullable | `'completed'` or NULL |
| competed_date | date, nullable | Completion date (note the misspelling in the schema) |
| active_status | int, default 1 | |
| topic_id | FK sm_lesson_topics | Parent topic group |
| created_by / updated_by / school_id / academic_id / user_id | | **academic_id = academic-year scoped** |
| timestamps | | |

Global scope: `StatusAcademicSchoolScope`. Relations: `lesson_title()`
(belongsTo SmLesson), `lessonPlan()` (hasMany `LessonPlanTopic` on topic_id).

### 1.5 `lesson_planners` — Daily lesson planner (planning layer)

One row per planned delivery: a teacher teaching a subject to a class+section
in a specific period on a specific date. Carries all the delivery/pedagogy
fields and its own completion status.

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| day | int, nullable | `1=sat … 7=fri` (week-day index) |
| active_status | tinyint, default 1 | |
| lesson_id | int, nullable | The lesson |
| lesson_detail_id | int | The chosen lesson (drives topic dropdown) |
| topic_id | int, nullable | Selected topic (non-customize mode) |
| topic_detail_id | int, nullable | Selected topic detail |
| sub_topic | string, nullable | Free-text sub-topic (non-customize mode) |
| lecture_youube_link | text | YouTube link (schema misspelling) |
| lecture_vedio | text | Video (schema misspelling) |
| attachment | text | Uploaded document |
| teaching_method | text | |
| general_objectives | text | |
| previous_knowlege | text | (schema misspelling) |
| comp_question | text | Comprehensive questions |
| zoom_setup | text | |
| presentation | text | |
| note | text | |
| lesson_date | date, NOT NULL | The planned date |
| competed_date | date, nullable | Completion date |
| completed_status | string, nullable | `'completed'` or NULL |
| room_id | FK sm_class_rooms | |
| teacher_id | FK sm_staffs | |
| class_period_id | FK sm_class_times | Period/slot |
| subject_id / class_id / section_id | FK | |
| created_by / updated_by | int | |
| routine_id | int, nullable | FK to class routine slot that spawned it |
| school_id / academic_id | FK | **academic_id = academic-year scoped** |

Global scope: `StatusAcademicSchoolScope`. Relations: `class`, `sectionName`,
`subject`, `lessonName` (belongsTo SmLesson via lesson_detail_id), `topicName`
(belongsTo SmLessonTopicDetail via topic_detail_id), `teacherName`,
`topics()` (hasMany LessonPlanTopic).

### 1.6 `lesson_plan_topics` — Sub-topics under a planner (customize mode)

Used only in "customize" mode where a single planner row carries multiple
topic + sub-topic pairs.

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| sub_topic_title | string | |
| topic_id | FK sm_lesson_topic_details | The topic this sub-topic belongs to |
| lesson_planner_id | FK lesson_planners | |
| timestamps | | |

Model `LessonPlanTopic`: `topicName` (belongsTo SmLessonTopicDetail),
`lessonDetail` (belongsTo LessonPlanner). **No `StatusAcademicSchoolScope`** —
it is not academic-scoped directly (it inherits scope through its parent
planner).

### 1.7 Setting flag

`sm_general_settings.sub_topic_enable` toggles whether the sub-topic
(customize) feature is shown. Set via the lesson-plan Setting screen.

### Academic-year scoping summary

| Table | academic_id column | Enforced by |
|---|---|---|
| sm_lessons | yes | column + global scope |
| sm_lesson_topics | yes | column + global scope |
| sm_lesson_topic_details | yes | column + global scope |
| lesson_planners | yes | column + global scope |
| lesson_plan_topics | no (inherited) | via parent planner |
| sm_lesson_details | yes (legacy/unused) | — |

Every primary table also carries `school_id` for multi-tenant separation.

---

## 2. Creating a Lesson / Chapter and hanging Topics under it

### Lesson creation (`SmLessonController::storeLesson`)

1. Teacher/admin selects **class + subject** (+ shift if enabled) and types one
   or more lesson titles (the form posts `lesson[]`, an array of titles).
2. The system looks up **every section** assigned that class+subject via
   `sm_assign_subjects` (`SmAssignSubject::where(class_id, subject_id)`).
3. It then **nested-loops sections × lesson titles**, inserting one `sm_lessons`
   row for each (class, section, subject, lesson_title, shift, academic_id,
   school_id, user_id). So creating "Chapter 1, Chapter 2" for a class with 3
   sections produces 6 rows.
4. `academic_id = getAcademicId()`, `school_id = auth user's school`.

Edit (`updateLesson`) rewrites titles positionally over the existing rows and
appends new ones if the count grew. Delete cascades manually to
`sm_lesson_topics`, `sm_lesson_topic_details`, and `lesson_planners` for that
lesson.

Teacher visibility is restricted: role_id 4 (teacher) only sees lessons for the
subjects assigned to them (`SmAssignSubject` filter).

### Topic creation (`SmTopicController::store`)

1. Selects **class + section + subject + lesson** and types one or more topic
   titles (`topic[]`).
2. **Deduplication:** it checks for an existing `sm_lesson_topics` group row for
   that school+class+lesson+section+subject(+shift)+academic. 
   - If one exists → it only appends new `sm_lesson_topic_details` rows under
     that existing `topic_id`.
   - If none → it creates one `sm_lesson_topics` group row, then inserts each
     topic title as a `sm_lesson_topic_details` row with `topic_id` = the new
     group id and `lesson_id` = the selected lesson.
3. Each topic detail is created **without** a completion status (NULL =
   incomplete by default).

So the hierarchy is:

```
sm_lessons (Lesson / Chapter: class+section+subject, title)
   └─ sm_lesson_topics (one group row per lesson, holds class/section/subject)
         └─ sm_lesson_topic_details (Topic: topic_title, completed_status, competed_date)   ← completion unit
```

And the planning layer overlays it:

```
lesson_planners (a dated period slot: teacher+class+section+subject+date+period)
   ├─ references lesson_detail_id (sm_lessons) + topic_detail_id (sm_lesson_topic_details)
   └─ lesson_plan_topics (customize mode: many topic + sub_topic pairs per planner)
```

### Daily planner creation (`LessonPlanController::addNewLessonPlan`)

Driven from a weekly timetable grid keyed off the class routine
(`routine_id`). For a given day/date/period the teacher picks a lesson, then a
topic (or, in **customize** mode, multiple topic+sub-topic pairs), fills in
pedagogy fields (teaching method, objectives, previous knowledge,
comprehensive questions, Zoom setup, presentation, note, YouTube link,
attachment), and saves one `lesson_planners` row.
- Non-customize: `topic_id`, `topic_detail_id`, `sub_topic` stored directly on
  the planner.
- Customize: for each posted topic, a `lesson_plan_topics` row (topic_id +
  sub_topic_title + lesson_planner_id) is created.
- On save, notifications are dispatched to the teacher and to the
  class+section's students/parents.

---

## 3. Completion & Progress tracking

There are **two independent completion mechanisms**:

### 3.1 Topic completion (curriculum)

`LessonPlanController::completeStatus` (AJAX) marks a **topic**
(`sm_lesson_topic_details`) complete/incomplete:

```php
$topic = SmLessonTopicDetail::find($request->topic_id);
if ($request->cancel === 'incomplete') {
    $topic->competed_date    = null;
    $topic->completed_status = null;          // → incomplete
} else {
    $topic->competed_date    = date('Y-m-d', strtotime($request->complete_date));
    $topic->completed_status = 'completed';   // → complete on a chosen date
}
$topic->save();
```

- A topic is **complete** iff `completed_status = 'completed'` AND
  `competed_date` is set; otherwise incomplete (both NULL).
- Shown in the topic-overview screen (`manage_lesson`), which lists each topic
  detail with its completion date when set.
- No stored percentage at the topic layer — completeness is per-topic boolean.

### 3.2 Lesson-planner completion + progress percentage (planning)

`LessonPlanStatus` / `lessonPlanstatusAjax` mark a **planner row**
(`lesson_planners`) complete/incomplete using the same
`completed_status = 'completed'` + `competed_date` pair.

**Progress percentage** is computed on the planning layer in
`searchLessonPlan` (Manage Lesson Planner) and `searchlessonPlanReport`
(Lesson Plan Report):

```php
$total           = LessonPlanner::where(teacher, class, section, subject)->count();
$completed_total = LessonPlanner::where('completed_status','completed')
                        ->where(teacher, class, section, subject)->count();
$percentage      = $total > 0 ? $completed_total / $total * 100 : 0;
```

i.e. **progress = completed planners / total planners × 100**, filtered by
teacher + class + section + subject (the report variant filters by
teacher + subject only). This is the headline "syllabus completion %" shown to
admins and teachers.

> Note: the built-in percentage is over **lesson_planners**, not over topics.
> A topic-level percentage (completed topic details / total) is not stored but
> is trivially derivable from `sm_lesson_topic_details.completed_status`.

---

## 4. Calendar / date planning per topic

- The planning layer is **calendar-driven**: `lesson_planners.lesson_date` +
  `day` (1=sat…7=fri) + `class_period_id` place each plan on a specific date
  and period. The UI renders a **weekly grid** (Sat–Fri, or configurable start
  day via `sm_general_settings.week_start_id`) built with Carbon
  `CarbonPeriod`, with next/previous-week navigation
  (`changeWeek` / `discreaseChangeWeek`).
- Each grid cell comes from the class routine (`routine_id` →
  `sm_class_routine_update`), so the plan is pre-seeded by the timetable:
  teacher, class, section, subject, room, period.
- Per-topic dates: a topic's own completion date lives on
  `sm_lesson_topic_details.competed_date`; a planner's planned date is
  `lesson_date` and its completion date is `competed_date`.
- Student/parent/teacher "overview" screens re-render the same weekly calendar
  read-only.

---

## 5. Clean single-school rebuild recommendation

Collapse the six tables (two of them legacy/duplicated) into a lean model.
**Drop `school_id` entirely** (single school). **Keep academic-year scoping.**

### Recommended tables

**`lessons`** (chapter header — one row per class+section+subject+title)
```
id
academic_year_id      FK  -- REQUIRED, all queries scoped by it
class_id              FK
section_id            FK
subject_id            FK
title                 string          -- was lesson_title
position              int  nullable   -- ordering of chapters
is_active             bool default true
created_by            FK users
timestamps
```

**`lesson_topics`** (the topic = completion unit; folds
`sm_lesson_topics` + `sm_lesson_topic_details` into one table)
```
id
lesson_id             FK lessons  (cascade delete)
academic_year_id      FK
title                 string          -- was topic_title
position              int  nullable
is_complete           bool default false   -- replaces completed_status='completed'
completed_on          date nullable        -- was competed_date
is_active             bool default true
created_by / updated_by  FK users
timestamps
```
- Mark complete: `is_complete = true, completed_on = <date>`.
- Mark incomplete: `is_complete = false, completed_on = null`.

**`lesson_plans`** (daily planner — the calendar/pedagogy layer; keep only if
you need the timetable-driven daily plan; otherwise omit)
```
id
academic_year_id      FK
class_id / section_id / subject_id  FK
teacher_id            FK
lesson_id             FK lessons
topic_id              FK lesson_topics  nullable
routine_id            FK  nullable
lesson_date           date
day                   tinyint  (1..7)
period_id             FK class_times nullable
room_id               FK nullable
-- pedagogy
teaching_method / general_objectives / previous_knowledge /
comprehension_questions / zoom_setup / presentation / note   text nullable
youtube_link          string nullable
attachment            string nullable
is_complete           bool default false
completed_on          date nullable
created_by / updated_by  FK
timestamps
```

**`lesson_plan_subtopics`** (only if the sub-topic feature is wanted)
```
id
lesson_plan_id        FK lesson_plans (cascade)
topic_id              FK lesson_topics
title                 string          -- was sub_topic_title
position              int nullable
timestamps
```

### Progress model

- **Topic-level (recommended headline):**
  `progress% = complete_topics / total_topics × 100`, scoped to a
  lesson (or to class+section+subject), where a topic counts complete iff
  `is_complete = true`. Cheap boolean count — no stored aggregate needed.
- **Plan-level (optional, mirrors reference):**
  `progress% = complete_lesson_plans / total_lesson_plans × 100` for a
  teacher+class+section+subject.

### Cleanups vs. the reference system

- Drop `sm_lesson_details` (dead/duplicate) and `sm_lesson_topics` as a
  separate grouping table — fold topics directly under `lessons`.
- Drop all `school_id` columns and the multi-school global scope; keep a single
  `academic_year_id` scope helper.
- Rename the misspelled columns: `competed_date → completed_on`,
  `previous_knowlege → previous_knowledge`,
  `lecture_youube_link → youtube_link`, `lecture_vedio → video`,
  `comp_question → comprehension_questions`.
- Replace the string `completed_status='completed'` sentinel with a real
  `is_complete` boolean plus `completed_on` date.
- Enforce FKs with real `onDelete('cascade')` instead of manual loop deletes.
```

---

### Lesson + Topic + Completion rules (summary)

1. A **Lesson/Chapter** (`sm_lessons`) is one title per class + section +
   subject; creating titles fans them out across every assigned section, and
   each row is academic-year and school scoped.
2. **Topics** live in `sm_lesson_topic_details` (grouped by a
   `sm_lesson_topics` row per lesson); each topic has a `topic_title` and hangs
   under its lesson via `lesson_id`/`topic_id`.
3. A **topic is complete** iff `completed_status = 'completed'` and
   `competed_date` is set (toggled by `completeStatus`); clearing both makes it
   incomplete.
4. A separate **daily lesson planner** (`lesson_planners`) overlays a
   calendar/timetable (date + day + period from the class routine) with its own
   `completed_status`/`competed_date` and optional sub-topics
   (`lesson_plan_topics`).
5. **Progress %** in the reference system is
   `completed lesson_planners / total × 100` (per teacher+class+section+subject);
   the rebuild should instead use `complete topics / total topics × 100` with a
   real `is_complete` boolean.
