# 18 - Download Center (Shared Documents / Study Content)

> Source: the reference system (Laravel school-management), READ-ONLY analysis.
> Two parallel implementations exist for "downloadable content". Both are documented
> because the clean rebuild merges their best ideas.

---

## 1. Overview

The reference system ships **two overlapping mechanisms** for distributing files/documents to
users by role and by class/section:

| # | Name | Tables | Targeting model | Notes |
|---|------|--------|-----------------|-------|
| A | **Legacy "Upload Content" / Study Material** (core `app/`) | `sm_teacher_upload_contents`, `sm_content_types` | Per-item: roles (admin/student) + optional class/section | Content **type is a fixed 4-code enum** (assignment / study material / syllabus / other). This is the classic "Download Center". |
| B | **DownloadCenter module** (`Modules/DownloadCenter`) | `contents`, `content_types`, `content_share_lists`, `video_uploads` | Two-step: build a file library, then create a **share** that targets a Group/Class/Individual/Public link | Content type is a free-text lookup table. Files uploaded once, shared many times. |

The clean single-school rebuild (Section 7) unifies these into **`content_types` + `download_contents`** with a single target-audience model.

---

## 2. System A - Legacy Upload Content / Study Material

### 2.1 Entities & columns

**`sm_content_types`** (content categories, academic-year scoped):

| Column | Type | Notes |
|--------|------|-------|
| id | int | PK |
| type_name | varchar(200) | e.g. "Worksheet", "Notes" |
| active_status | tinyint default 1 | status gate |
| created_by / updated_by | int | audit |
| school_id | int default 1 | multi-school (DROP in rebuild) |
| academic_id | int default 1 | **academic-year scoped** |

Model `App\SmContentType` applies a global `StatusAcademicSchoolScope` (auto-filters `active_status=1` + current academic year + school).

**`sm_teacher_upload_contents`** (the actual document/download):

| Column | Type | Purpose |
|--------|------|---------|
| id | int | PK |
| content_title | varchar(200) | title |
| content_type | varchar(191) | **fixed code**: `as`=assignment, `st`=study material, `sy`=syllabus, `ot`=others download (SQL column comment confirms) |
| available_for_admin | int default 0 | 1 = visible to admin/staff |
| available_for_all_classes | int default 0 | 1 = visible to every student (ignore class/section) |
| upload_date | date | publish/upload date |
| description | varchar(500) | |
| source_url | varchar(191) | external link alternative to file |
| upload_file | varchar(200) | stored file path |
| active_status | tinyint default 1 | status gate |
| class | int | target class (when targeted to students, not all-classes) |
| section | int | target section |
| shift_id | int | optional shift targeting |
| course_id / parent_course_id / chapter_id / lesson_id | int | LMS linkage (null for plain downloads) |
| created_by / updated_by | int | uploader/audit |
| school_id | int default 1 | multi-school (DROP) |
| academic_id | int default 1 | **academic-year scoped** |

> Note: the `content_type` string doubles as the **category enum**. `sm_content_types` is the
> generic lookup used elsewhere; the four download screens (Assignment / Study Material /
> Syllabus / Others) key off the hard-coded `as/st/sy/ot` codes.

Related enum `available_for` (a request field, not a stored column) is the multi-select of
target roles: `['admin', 'student']`. It is exploded into `available_for_admin` and the
class/section columns at save time.

### 2.2 Upload & targeting (`SmUploadContentController@store`)

1. Validated fields: `content_title`, `content_type`, `available_for` (array, required),
   `upload_date`, optional `content_file` (mimes: pdf,doc,docx,jpg,jpeg,png,mp4,mp3,txt;
   max = general-setting file size), optional `source_url` (must be URL), optional `description`.
2. If `available_for` contains **`student`** and `all_classes` is off, then `class` is **required**.
3. File saved to **`public/uploads/upload_contents/`** via `fileUpload()` helper; path stored in `upload_file`.
4. Target flags derived from `available_for`:
   - `admin` in list -> `available_for_admin = 1`.
   - `student` in list + `all_classes = on` -> `available_for_all_classes = 1` (class/section left null).
   - `student` in list + a class chosen -> store `class`, `section`, `shift_id`.
5. `academic_id = getAcademicId()`, `school_id = auth school`, `created_by = auth user`.
6. On save, **notifications** are fanned out: admins get an SmNotification linking to the
   relevant list; students (+parents) matching the class/section (or all students if all-classes)
   get Student/Parent notifications keyed to `student-assignment` / `student-study-material` /
   `student-syllabus` / `student-others-download` routes.

### 2.3 Visibility filter (who SEES what)

**Admin / Teacher lists** (`assignmentList`, `studyMetarialList`, `syllabusList`, `otherDownloadList`):
- Filter by `content_type` code + current `academic_id` + `school_id` + `course_id/chapter_id/lesson_id IS NULL` (exclude LMS-nested items).
- **Teachers** (`teacherAccess()`) see only rows where `created_by = me OR available_for_admin = 1`.
- Admins see all rows in the academic year.

**Student / Parent view** (e.g. `SyllabusController@studentSyllabus`, api v2):
```
SmTeacherUploadContent
  ->where('content_type', 'sy')                 // the category
  ->whereNull course_id / chapter_id / lesson_id // plain downloads only
  ->where('available_for_all_classes', 1)        // (see note)
  ->where('school_id', school)
  ->where('academic_id', record.academic_id)
  ->where(fn => class = record.class_id  OR class IS NULL)
  ->where(fn => section = record.section_id OR section IS NULL)
```
So a student sees an item when it is **all-classes OR matches their class (and section, if set)**,
within their academic year. Files served as `asset('/').upload_file`.

### 2.4 Status gating
- `active_status = 1` enforced by `StatusAcademicSchoolScope` on `SmContentType` (and used on content rows).
- Academic-year scope enforced everywhere via `getAcademicId()` / `GlobalAcademicScope`.
- Edit/Delete guarded: non-admins can only modify content they created (`created_by == auth id`).

---

## 3. System B - DownloadCenter Module

Module path: `Modules/DownloadCenter`. Two-step model: **(1) build a content library**,
**(2) share** selected items to an audience. Plus a separate **video (YouTube) list**.

### 3.1 `content_types` (migration `create_content_types_table`)

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | PK |
| name | varchar(100) | category name (free text) |
| description | text nullable | |
| academic_id | int nullable | **academic-year scoped** (FK sm_academic_years) |
| school_id | int nullable default 1 | multi-school (DROP) |

CRUD via `ContentTypeController` (name required, description optional). No status column.

### 3.2 `contents` (the file library - `create_contents_table`)

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | PK |
| file_name | varchar nullable | display name (original filename or YouTube title) |
| file_size | int nullable | bytes |
| content_type_id | int | FK content_types |
| youtube_link | varchar nullable | alternative to file |
| upload_file | varchar(200) nullable | stored path |
| uploaded_by | int | FK users |
| academic_id | int nullable | **academic-year scoped** |
| school_id | int nullable default 1 | (DROP) |

Upload (`ContentListController@contentListSave`):
- Either a `content_file` **or** a `youtube_link` (not both). File mimes:
  jpg,png,jpeg,pdf,doc,docx,txt,xlsx,rar,zip; max = general-setting file size.
- File stored to **`public/uploads/content_list/`**; `file_name`/`file_size` captured.
- YouTube: `file_name = getYoutubeName(link)`.
- `uploaded_by = auth id`. No class/role targeting here - targeting happens at share time.

### 3.3 `content_share_lists` (the share/distribution - `create_content_share_lists_table`)

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | PK |
| title | varchar nullable | |
| share_date | date nullable | publish date |
| valid_upto | date nullable | expiry (validation: after share_date) |
| description | text nullable | |
| **send_type** | varchar nullable | audience mode: `G`=Group(roles), `C`=Class, `I`=Individual, `P`=Public link |
| content_ids | json | which `contents` rows are shared (required) |
| gr_role_ids | json | target role ids (when send_type=G) |
| ind_user_ids | json | target user ids (when send_type=I) |
| class_id | int nullable | target class (when send_type=C) |
| section_ids | json | target sections (when send_type=C) |
| url | text nullable | random 30-char slug (when send_type=P) |
| shared_by | int nullable | FK users |
| academic_id | int nullable | **academic-year scoped** |
| school_id | int nullable default 1 | (DROP) |

Save logic (`ContentShareListController@contentShareListSave`): sets `send_type = selectTab`,
stores `content_ids`, then **only** the audience columns for that tab (role ids / user ids /
class+sections). Public link (`contentGenarteUrlSave`): `send_type='P'`, generates
`url = generateRandomString(30)`, reachable unauthenticated at
`download-center/content-share-link/{url}`.

### 3.4 Visibility filter (`contentShareList` for students, role_id === 2)

For each share row, a **student sees it** when ANY of:
- `send_type = 'G'` AND `2 ∈ gr_role_ids` (student role targeted as a group), OR
- `send_type = 'I'` AND `auth user id ∈ ind_user_ids`, OR
- `send_type = 'C'` AND `class_id == student.class_id` AND `student.section_id ∈ section_ids`.

Non-students (admin/teacher) see **all** share lists. Parents use `parentContentShareList($studentId)`
with the same three rules evaluated against the child's record (uses `student.user_id` for the Individual check).

### 3.5 `video_uploads` (YouTube video content - `create_video_uploads_table`)

| Column | Notes |
|--------|-------|
| id, title, description, youtube_link | video metadata |
| class_id, section_id, shift_id | direct class/section targeting (no share step) |
| created_by | uploader |
| un_* columns | University-module variant (session/faculty/dept/semester) - out of scope for single school |
| academic_id, school_id | academic-year scoped / multi-school (DROP school_id) |

Only YouTube links accepted (`youtubeVideoLinkValidation`). **Student visibility**: rows where
`class_id = student.class_id AND section_id = student.section_id`. Admin/teacher see all.

---

## 4. Academic-year scoping summary

**Academic-year scoped** (all carry `academic_id`): `sm_content_types`, `sm_teacher_upload_contents`,
`content_types`, `contents`, `content_share_lists`, `video_uploads`. Every list/visibility query filters
on the current `getAcademicId()`. `school_id` is present everywhere for multi-tenant and must be dropped
in a single-school rebuild.

---

## 5. Targeting model comparison

| Aspect | System A (legacy) | System B (module) |
|--------|-------------------|-------------------|
| Category | `content_type` 4-code enum + `sm_content_types` lookup | `content_types` free-text lookup |
| Role targeting | `available_for` -> admin flag + student class/section | `send_type=G` with `gr_role_ids` |
| Class/section | `class` + `section` columns (or all-classes flag) | `send_type=C` with `class_id`+`section_ids` |
| Individual user | (none) | `send_type=I` with `ind_user_ids` |
| Public/anon link | (none) | `send_type=P` with random `url` |
| File reuse | one file = one item | one file, shared many times |

---

## 6. Status / gating rules

- **System A**: `active_status=1` + academic-year + school enforced by global scopes; teachers restricted
  to own content or `available_for_admin=1`; owner-only edit/delete.
- **System B**: no active/status column; gating is purely the audience filter + `valid_upto` expiry date +
  academic-year scope. Public links bypass auth entirely (share-link route is outside the auth middleware).

---

## 7. Clean single-school rebuild recommendation

Merge both systems into **two tables**. Drop `school_id` everywhere; keep `academic_year_id`
(scope every query to the active year). Represent audience with a single explicit target model
instead of the scattered flags/`send_type` columns.

### 7.1 `content_types`
```
content_types
  id                bigint PK
  name              varchar(120)          -- e.g. Assignment, Syllabus, Study Material, Worksheet
  code              varchar(20) nullable  -- optional stable key (assignment/syllabus/study_material/other)
  description       text nullable
  is_active         boolean default true  -- status gate (from legacy active_status)
  academic_year_id  bigint FK             -- academic-year scoped
  created_at / updated_at
```
Seed the four canonical categories (assignment, study_material, syllabus, other) plus allow admins
to add more.

### 7.2 `download_contents`
```
download_contents
  id                bigint PK
  title             varchar(200)
  description       text nullable
  content_type_id   bigint FK content_types
  file_path         varchar(255) nullable   -- stored upload (uploads/download_center/)
  file_name         varchar(200) nullable   -- original name for display
  file_size         int nullable
  external_url      varchar(255) nullable   -- youtube/source URL alternative to file
  -- Targeting (single audience model) --
  target_audience   enum('all','admins','teachers','students','parents','class','individual')
  class_id          bigint nullable FK      -- when target_audience = class
  section_id        bigint nullable FK      -- optional narrower than class
  -- optional many-to-many for multi-role/multi-user/multi-section targeting:
  --   download_content_roles(content_id, role_id)
  --   download_content_users(content_id, user_id)
  --   download_content_sections(content_id, section_id)
  publish_date      date                    -- share/publish date
  valid_upto        date nullable           -- optional expiry
  is_active         boolean default true    -- status gate
  uploaded_by       bigint FK users
  academic_year_id  bigint FK               -- academic-year scoped
  created_at / updated_at
```

**Design decisions carried over:**
- Keep the **content_type category** (assignment/syllabus/study-material/other) as a FK, not a hard-coded string.
- Collapse System B's two-step (library + share) into **one row** unless file-reuse across many shares is
  genuinely needed; if it is, keep a `download_contents` (files) + `content_shares` split, but still drop
  `school_id` and use the `target_audience` enum instead of `send_type` letters.
- Support both an uploaded `file_path` and an `external_url` (covers YouTube/source-URL cases).

### 7.3 Rebuilt visibility filter (single source of truth)
Given the authenticated user (with role + optional class/section from their student/staff record):
```
DownloadContent
  ->where('academic_year_id', activeYear)
  ->where('is_active', true)
  ->where(fn => valid_upto IS NULL OR valid_upto >= today)
  ->where(function ($q) use ($user) {
       $q->where('target_audience', 'all')
         ->orWhere('target_audience', $user->role_group)          // admins/teachers/students/parents
         ->orWhere(fn => target_audience='class'
                          AND class_id = $user.class_id
                          AND (section_id IS NULL OR section_id = $user.section_id))
         ->orWhereHas('targetRoles',    fn => role_id = $user.role_id)   // if M2M kept
         ->orWhereHas('targetUsers',    fn => user_id = $user.id);       // individual
  });
```
Admins/uploaders see everything in the active year; students/parents/teachers see only rows whose
audience matches their role and (for class-targeted rows) their class/section. Parents resolve the
filter against their child's class/section record.

### 7.4 File storage
Single directory `storage/uploads/download_center/` (or `public/uploads/download_center/`), path stored
in `file_path`, original name in `file_name`. Validate mimes (pdf,doc,docx,xlsx,jpg,png,txt,zip,rar,mp4,mp3)
and max size from general settings. Delete file on row delete.
