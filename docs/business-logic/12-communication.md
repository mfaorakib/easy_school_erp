# 12 - Communication Business Logic

Documents how the reference system models school communication: the notice board,
events (calendar), and the send-message / email-SMS delivery pipeline, plus the
per-user notification feed. Ends with a clean single-school rebuild recommendation.

The core targeting idea across all three features is the same: a record stores a
**JSON array of role IDs** it is addressed to, and each audience role reads its
notices/events with a `LIKE '%<role_id>%'` (or `whereJsonContains`) filter.

Role IDs referenced throughout: `1` = Admin/Super Admin, `2` = Student,
`3` = Parent/Guardian, `4` = Teacher, plus a dynamic Alumni role id
(`GlobalVariable::isAlumni()`).

---

## 1. Entities & Tables

| Feature | Table | Model | Purpose |
|---|---|---|---|
| Notice board | `sm_notice_boards` | `App\SmNoticeBoard` | Published notices targeted at roles |
| Events | `sm_events` | `App\SmEvent` | Calendar/events targeted at roles |
| Email/SMS log | `sm_email_sms_logs` | `App\SmEmailSmsLog` | Audit log of each send-email/SMS batch |
| Send message (legacy) | `sm_send_messages` | `App\SmSendMessage` | Legacy/unused notice-like table (see §5) |
| Per-user notifications | `sm_notifications` | `App\SmNotification` | In-app bell feed rows (one per recipient user) |

---

## 2. Notice Board (`sm_notice_boards`)

The primary communication feature. A single notice row is written once and made
visible to one or more roles.

| Column | Type | Notes |
|---|---|---|
| `id` | int unsigned PK | |
| `notice_title` | varchar(200) | Title |
| `notice_message` | text | Body of the notice |
| `notice_date` | date | The notice's own date |
| `publish_on` | date | **Publish date** — notice is only listed once `publish_on <= today` |
| `inform_to` | varchar(200), comment "Notice message sent to these roles" | **Audience** — a JSON array of role IDs, e.g. `[2,3]` |
| `active_status` | tinyint (default 1) | Soft on/off flag |
| `is_published` | int (default 0) | Draft/published flag |
| `created_at` / `updated_at` | timestamp | |
| `created_by` / `updated_by` | int unsigned (default 1) | Audit user |
| `school_id` | int unsigned (default 1) | Multi-tenant scope |
| `academic_id` | int unsigned (default 1) | Academic-year scope (`un_academic_id` used instead when University module active) |

### 2.1 Audience / visibility model

- There are **NO per-role boolean columns** (no `visible_student` /
  `visible_parent` flags). The entire audience is a single JSON array of role IDs
  stored in `inform_to`.
- On create (`SmNoticeController::saveNoticeData`):
  `$notice->inform_to = json_encode($request->role);` where `$request->role` is
  the array of selected role IDs from the form (roles offered come from
  `InfixRole`, excluding SaaS roles; guardian role `3` is hidden unless
  `generalSetting()->with_guardian == 1`).
- Sample data confirms the shape: `inform_to = '[1]'`.

### 2.2 Scope

- Model boots `StatusAcademicSchoolScope` (global scope). For a logged-in user
  this auto-filters every query by `active_status = 1`, `academic_id = getAcademicId()`,
  and `school_id = user.school_id`.
- **Scope: school-scoped AND academic-year-scoped.**

### 2.3 Admin listing

`noticeList()` shows `publish_on <= today`, newest first (the global scope already
constrains school + academic year + active status).

---

## 3. How each role SEES its notices (the role-visibility filter)

This is the crux of the audience model. When a student/parent/teacher app fetches
notices, the query filters the JSON `inform_to` for that user's role id:

From `api\v2\NoticeBoard\NoticeBoardController::studentNoticeboard`:

```php
// role_id == 2 (Student)
SmNoticeBoard::withoutGlobalScopes([StatusAcademicSchoolScope::class])
    ->where('active_status', 1)
    ->where('inform_to', 'LIKE', '%2%')          // <-- role filter
    ->where('academic_id', SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
    ->where('school_id', auth()->user()->school_id)
    ->orderBy('id', 'DESC')->get();
// role_id == 3 (Parent) -> same with 'inform_to' LIKE '%3%'
```

- The visible set for a role = notices whose `inform_to` JSON string contains that
  role's id (`LIKE '%<id>%'`), still scoped to the same school + academic year.
- Events use the same idea with a proper JSON predicate — `getAllEventList()` does
  `whereJsonContains('role_ids', (string) auth()->user()->roles->id)` for any
  non-admin (role id 1 sees all).
- Note the `LIKE '%2%'` approach is substring matching (a known fragility: id `2`
  would also match `12`, `20`, etc.); `whereJsonContains` (used by events) is the
  correct form and is the recommended pattern for the rebuild.

---

## 4. Events (`sm_events`)

| Column | Type | Notes |
|---|---|---|
| `id` | int unsigned PK | |
| `event_title` | varchar(200) | Title |
| `for_whom` | varchar(200), comment "teacher, student, parents, all" | Legacy audience label (free text) |
| `role_ids` | text | **Audience** — JSON array of role IDs (the live targeting field) |
| `url` | text | Optional link |
| `event_location` | varchar(200) | Location |
| `event_des` | longtext | Description |
| `from_date` | date | **Start date** |
| `to_date` | date | **End date** |
| `uplad_image_file` | varchar(200) | Uploaded image path (column misspelled "uplad") |
| `active_status` | tinyint (default 1) | Soft on/off flag |
| `created_at` / `updated_at` | timestamp | |
| `created_by` / `updated_by` | int unsigned (default 1) | Audit user |
| `school_id` | int unsigned (default 1) | Multi-tenant scope |
| `academic_id` | int unsigned (default 1) | Academic-year scope (`un_academic_id` when University active) |

- On store (`SmEventController::store`): `role_ids = json_encode($request->role_ids)`;
  dates stored as `Y-m-d`.
- Model `App\SmEvent` boots `StatusAcademicSchoolScope`.
- **Scope: school-scoped AND academic-year-scoped.**
- `for_whom` is a legacy descriptive column; actual audience targeting is driven by
  the JSON `role_ids` array (filtered via `whereJsonContains` per §3).

---

## 5. Send message / delivery

### 5.1 "Send message" = create a notice

The admin menu item "Send Message" maps to `SmNoticeController::sendMessage()`,
which just renders the notice form (`backEnd.communicate.sendMessage`) populated
with selectable roles. Submitting it runs `saveNoticeData` — i.e. **sending a
message and creating a notice are the same operation**; there is no separate
delivery record for it beyond the notice row + notifications (below).

The `sm_send_messages` table (`message_title`, `message_des`, `notice_date`,
`publish_on`, `message_to` = roles, + audit/scope columns) mirrors the notice-board
shape but its model `App\SmSendMessage` is **empty and unused** in the codebase —
treat it as legacy/dead. The rebuild does not need it.

### 5.2 Fan-out to the notification feed (`sm_notifications`)

After a notice or event is saved, the controller loops each targeted role, loads
all active `User`s with that `role_id`, and:

- calls the `NotificationSend` trait (`sent_notifications('Notice'|'Event', $userIds, $data, ['Student'|'Parent'|'Teacher'|'Alumni'])`) to push push/FCM notifications, and
- inserts one `SmNotification` row **per recipient user** (`role_id`, `message`,
  `date`, `user_id`, `url = 'notice-list'`, `school_id`, `academic_id`).

`SmNotification::notifications()` returns the current user's unread feed
(`user_id = me`, `role_id = my role`, `is_read = 0`).

### 5.3 Email / SMS blast (`SmCommunicateController`) and its log

Separate from notices, admins can blast email or SMS via
`SmCommunicateController::sendEmailSms`. Targeting supports three modes
(`selectTab`): `G` = **G**roup by role(s), `I` = **I**ndividual recipients,
section = class/section student+parent selection. `send_through` = `E` (email) or
`S` (SMS, via active SMS gateway / Mobile-SMS FCM job).

Every batch writes ONE audit row via `SmEmailSmsLog::saveEmailSmsLogData`:

**`sm_email_sms_logs`**

| Column | Type | Notes |
|---|---|---|
| `id` | int unsigned PK | |
| `title` | varchar(191) | From `email_sms_title` |
| `description` | varchar(191) | Message body |
| `send_date` | date | `date('Y-m-d')` at send time |
| `send_through` | varchar(191) | `E` = email, `S` = SMS |
| `send_to` | varchar(191) | Target mode: `G` / `I` / section (defaults to `G`) |
| `active_status` | tinyint (default 1) | |
| `created_at` / `updated_at` | timestamp | |
| `created_by` / `updated_by` | int unsigned (default 1) | Audit user |
| `school_id` | int unsigned (default 1) | Multi-tenant scope |
| `academic_id` | int unsigned (default 1) | Academic-year scope |

- The log stores the **batch summary only** (title, body, channel, target mode,
  date) — NOT the per-recipient address list. Recipients are resolved live at send
  time from students/parents/staff/alumni tables and are not persisted.
- `emailSmsLog()` lists logs filtered by `academic_id` + `school_id`, newest first.

---

## 6. Clean single-school rebuild recommendation

Drop the multi-tenant `school_id` everywhere. **Keep academic-year scoping**
(`academic_year_id`) on all four tables. Replace substring `LIKE` role matching
with a proper JSON/array-contains predicate.

### `notices`
```
id                PK
title             varchar
body              text
notice_date       date
publish_on        date          -- list only when publish_on <= today
audience          json          -- array of role ids/keys, e.g. ["student","parent"]
is_published      boolean default false
active_status     boolean default true
academic_year_id  FK
created_by / updated_by / timestamps
```
- Single `audience` JSON/array field (or a `notice_recipients` join table if you
  prefer relational targeting) — do NOT use per-role boolean columns.
- Read filter for a role: `audience @> '["student"]'` / `whereJsonContains`.

### `events`
```
id, title, description, location, url, image_path
from_date date, to_date date
audience json           -- same targeting model as notices; drop legacy for_whom
active_status boolean, academic_year_id FK, audit + timestamps
```

### `message_logs` (email/SMS audit)
```
id, title, body, send_date date
channel enum(email, sms)          -- was send_through E/S
target_mode enum(group, individual, section)  -- was send_to G/I
academic_year_id FK, created_by, timestamps
```
- Keep as a batch-summary audit row. Optionally add a `message_recipients` child
  table if per-recipient delivery status is needed (the reference system does not
  persist recipients).

### `notifications` (in-app feed)
```
id, user_id FK, role, title/message, url, date, is_read boolean default false,
academic_year_id FK, timestamps
```
- One row per recipient user, fanned out when a notice/event is published.

- **Drop** the legacy `sm_send_messages` table — "send message" is just creating a
  notice; unify them into `notices`.

---

## Summary (notice audience + event + message rules)

1. A notice (`sm_notice_boards`) stores its audience as a JSON array of role IDs in
   one column `inform_to` (e.g. `[2,3]`) — no per-role boolean flags — plus
   `notice_title`, `notice_message`, `notice_date`, and a `publish_on` date that
   gates when it becomes visible.
2. A user of a given role sees a notice when `inform_to` contains their role id
   (`LIKE '%<role_id>%'`; events use the sturdier `whereJsonContains('role_ids', id)`),
   further scoped to their school + academic year (roles: 1 Admin, 2 Student,
   3 Parent, 4 Teacher, + Alumni).
3. Events (`sm_events`) mirror this: `role_ids` JSON audience, `event_title`,
   `event_des`, `event_location`, `from_date`/`to_date`, image, academic-year scoped;
   admins (role 1) see all, others see only events targeting their role.
4. "Send message" is not a distinct entity — it reuses the notice-board create flow;
   on save the controller fans out one `sm_notifications` row per targeted user and
   fires push notifications. The `sm_send_messages` table/model is legacy and unused.
5. Email/SMS blasts are logged once per batch in `sm_email_sms_logs` (title, body,
   `send_through` E/S, `send_to` G/I/section, date, school + academic year) — a
   summary audit only; recipient addresses are resolved live and not persisted.
