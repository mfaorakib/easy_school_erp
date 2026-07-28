# 14 — Dormitory / Hostel

Business-logic reference for the Dormitory (hostel) module, reconstructed from the
reference system (read-only analysis). This documents *what the reference system does*,
followed by a clean single-school rebuild recommendation for easyschool-erp.

---

## 1. Entities & Tables

The reference system models the hostel with three master tables plus two foreign-key
columns placed directly on the student row. There is **no** dedicated
student-to-room assignment table.

### 1.1 Dormitory — `sm_dormitory_lists`

The building / hostel block.

| Column           | Type                | Notes |
|------------------|---------------------|-------|
| `id`             | int unsigned PK     | |
| `dormitory_name` | varchar(200)        | Display name |
| `type`           | varchar(191)        | `B` = Boys, `G` = Girls (gender restriction) |
| `address`        | varchar(191) null   | |
| `intake`         | int null            | Nominal total intake for the whole dormitory (informational; **not enforced**) |
| `description`    | text null           | Free note |
| `active_status`  | tinyint default 1   | Soft active flag |
| `created_by` / `updated_by` | int unsigned | Audit |
| `school_id`      | int unsigned default 1 | Multi-tenant scope |
| `academic_id`    | int unsigned default 1 | Academic-year scope |

Model `SmDormitoryList`: applies a global `ActiveStatusSchoolScope` (auto-filters by
`active_status` + `school_id`, and academic year). Assigned in the controller with
`school_id = Auth::user()->school_id` and `academic_id = getAcademicId()` on create.

### 1.2 Room Type — `sm_room_types`

A lookup/category (e.g. "AC", "Non-AC", "Single", "Shared").

| Column          | Type              | Notes |
|-----------------|-------------------|-------|
| `id`            | int unsigned PK   | |
| `type`          | varchar(255)      | The type name |
| `description`   | text null         | |
| `active_status` | tinyint default 1 | |
| `created_by` / `updated_by` | int unsigned | Audit |
| `school_id`     | int unsigned default 1 | |
| `academic_id`   | int unsigned default 1 | |

Model `SmRoomType`: casts `type` to string; global `ActiveStatusSchoolScope`.

### 1.3 Room — `sm_room_lists`

An individual room, belonging to one dormitory and one room type.

| Column          | Type                | Notes |
|-----------------|---------------------|-------|
| `id`            | int unsigned PK     | |
| `name`          | varchar(255)        | Room name / number |
| `number_of_bed` | int NOT NULL        | **Capacity** = number of beds in the room |
| `cost_per_bed`  | double null         | Cost per bed (hostel fee amount) |
| `description`   | text null           | |
| `dormitory_id`  | int unsigned        | FK → `sm_dormitory_lists.id` |
| `room_type_id`  | int unsigned        | FK → `sm_room_types.id` |
| `active_status` | tinyint default 1   | |
| `created_by` / `updated_by` | int unsigned | Audit |
| `school_id`     | int unsigned default 1 | |
| `academic_id`   | int unsigned default 1 | |

Model `SmRoomList` relationships:
- `dormitory()` → `belongsTo(SmDormitoryList, 'dormitory_id')`
- `roomType()` → `belongsTo(SmRoomType, 'room_type_id')`
- Global `ActiveStatusSchoolScope`.

### 1.4 Scope summary (reference system)

All three master tables carry **both** `school_id` (multi-tenant) and `academic_id`
(academic-year). So in the reference system, dormitories, room types, and rooms are
**academic-year scoped** — a fresh set is expected per academic year. None are truly
"global"; the closest to global is room *type*, which is effectively a reusable lookup
but is still stamped with `school_id` + `academic_id`.

---

## 2. Room → Dormitory, Capacity, and Cost

- A **room belongs to exactly one dormitory** via `sm_room_lists.dormitory_id`.
- A room's **capacity is `number_of_bed`** (an integer count of beds).
- A room's **cost is `cost_per_bed`** (a per-bed monetary amount).
- A room also references a **room type** via `room_type_id` (categorization only).

The controller `store()`/`update()` (SmRoomListController) simply persists
`name`, `dormitory_id`, `room_type_id`, `number_of_bed`, `cost_per_bed`, `description`
plus `school_id` and `academic_id`. There is **no validation** that beds are available
or that occupancy is below `number_of_bed` at any point.

Note: the student model also exposes a convenience relation
`rooms()` = `hasMany(SmRoomList, 'dormitory_id', 'dormitory_id')` — i.e. "all rooms in
the same dormitory the student is in" (used to populate room dropdowns), not an
assignment record.

---

## 3. Student Assignment

Assignment is stored **directly on the student row** (`sm_students`), not in a join
table:

| Column on `sm_students` | Type                | Meaning |
|-------------------------|---------------------|---------|
| `dormitory_id`          | int unsigned null   | FK → `sm_dormitory_lists.id` |
| `room_id`               | int unsigned null   | FK → `sm_room_lists.id` |

Student model `SmStudent` relationships:
- `dormitory()` → `belongsTo(SmDormitoryList, 'dormitory_id', 'id')`
- `room()` → `belongsTo(SmRoomList, 'room_id', 'id')`

Assignment happens during **student admission / edit** (SmStudentAdmissionController):

```php
$smStudent->dormitory_id = $request->dormitory_name;  // dormitory select
$smStudent->room_id      = $request->room_number;      // room select
```

Both are optional. There is exactly **one dormitory + one room per student**, held on
the current student record. Because the assignment lives on the student row (which is
itself academic-year scoped through the student's record for the year), the assignment
is effectively tied to the student's academic context — but there is **no separate
academic-year-scoped assignment history table**. Re-assigning simply overwrites
`dormitory_id` / `room_id`.

### 3.1 Capacity / Occupancy Enforcement — NONE

There is **no occupancy or capacity enforcement anywhere** in the reference system:

- Admission does not count how many students already have a given `room_id`.
- `number_of_bed` is never compared against current occupancy.
- Multiple students can be assigned to the same room beyond its bed count with no
  error or warning. (The only `capacity` check in the codebase is for *class rooms*
  in the class-routine feature, which is unrelated to the hostel module.)
- The dormitory `type` (Boys/Girls) is a stored attribute but is **not enforced**
  against student gender at assignment time.

Deletion guards are the only integrity checks: a dormitory/room cannot be deleted
while a student still references it (checked via `SmStudent::where('dormitory_id', $id)`
and a generic `tableList` used-in check).

---

## 4. Room Cost & Fees

`cost_per_bed` on the room is the monetary figure representing the hostel charge for a
bed. In the reference system this is stored as a plain attribute on the room; the
dormitory module itself does **not** automatically post this into the fees ledger — it
is a reference amount that the fees/collection side can read when building hostel
charges. (No automatic invoice generation is wired from the dormitory tables in the
module's own controllers.)

---

## 5. Clean Single-School Rebuild Recommendation (easyschool-erp)

Drop the multi-tenant `school_id` entirely (single-school product). Keep academic-year
scoping where it matters, add real occupancy enforcement, and normalize the student
assignment into its own table so history and capacity are trackable.

### 5.1 `room_types`
Reusable lookup — keep global (not year-scoped); types rarely change per year.

```
room_types
  id            PK
  name          varchar        -- was `type`
  description   text null
  is_active     boolean default true
  timestamps
```

### 5.2 `dormitories`

```
dormitories
  id            PK
  name          varchar
  gender        enum('boys','girls','mixed')   -- was `type` B/G
  address       varchar null
  note          text null
  is_active     boolean default true
  timestamps
```

### 5.3 `dormitory_rooms`

```
dormitory_rooms
  id             PK
  dormitory_id   FK -> dormitories.id      (cascade / restrict on delete)
  room_type_id   FK -> room_types.id
  room_no        varchar                    -- was `name`
  capacity       int NOT NULL               -- was `number_of_bed`  (beds)
  cost           decimal(10,2) null         -- was `cost_per_bed`
  note           text null
  is_active      boolean default true
  timestamps
  UNIQUE (dormitory_id, room_no)
```

### 5.4 `student_dormitories` (academic-year-scoped assignment)

Replace the `dormitory_id` / `room_id` columns on the student row with a dedicated,
year-scoped assignment table so a student can hold different assignments across years
and occupancy can be counted per room per year.

```
student_dormitories
  id                PK
  student_id        FK -> students.id
  dormitory_room_id FK -> dormitory_rooms.id
  academic_year_id  FK -> academic_years.id
  assigned_on       date null
  released_on       date null                -- null = currently occupying
  timestamps
  UNIQUE (student_id, academic_year_id)      -- one active room per student per year
  INDEX (dormitory_room_id, academic_year_id)
```

(The `dormitory_id` is reachable through `dormitory_room_id`; store it too only if you
want denormalized filtering.)

### 5.5 Capacity / occupancy enforcement (the key improvement)

On create/update of a `student_dormitories` assignment:

1. Count current active occupants of the target room for that academic year:
   `SELECT COUNT(*) FROM student_dormitories
    WHERE dormitory_room_id = ? AND academic_year_id = ? AND released_on IS NULL`
   (excluding the row being edited).
2. Reject if `count >= dormitory_rooms.capacity` → "Room is full".
3. Optionally enforce `dormitories.gender` against student gender.
4. Wrap in a transaction / use a row lock (or unique guard) to avoid two concurrent
   admissions overfilling the last bed.

Expose a computed `beds_available = capacity - active_occupants` for room dropdowns so
full rooms are hidden or disabled at selection time.

### 5.6 Fees tie-in
Keep `dormitory_rooms.cost` as the reference hostel charge. When a student is assigned,
the fees module can read `cost` to generate a hostel fee line for that student/year —
make this an explicit, opt-in step rather than an implicit trigger.
