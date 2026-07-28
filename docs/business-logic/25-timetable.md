# Timetable / Class Routine — Business-Logic Spec

> The reference system's routine is split between a denormalized `sm_class_routines`
> (monday/tuesday… columns) and the newer normalized `sm_class_routine_updates`,
> plus `sm_class_times` (period slots) and `sm_class_rooms`. EasySchool rebuilds
> the normalized model only.

## Entities & tables (all academic-year scoped)

| # | Table | Model | Purpose |
|---|---|---|---|
| 1 | `class_periods` | `ClassPeriod` | A daily time slot ("1st Period", 09:00–09:45). `is_break` marks tiffin/recess. |
| 2 | `classrooms` | `Classroom` | A physical room (room_no, capacity). |
| 3 | `timetable_entries` | `TimetableEntry` | One grid cell: class/section on a `day` at a `class_period` → subject (+ optional teacher, room). |

## The grid model

The weekly timetable is **rows = periods, columns = working days**
(`saturday…thursday`; Friday is the default weekend). Each cell is a
`TimetableEntry` uniquely keyed by **(class, section, day, period, year)**. The
service assembles a grid as `{periods, days, entries}` where `entries` is keyed
`"{periodId}|{day}"` for O(1) cell lookup in the view.

## Business rules

- **One entry per slot.** A class/section can hold at most one subject per (day,
  period) — enforced by a unique key. Saving the builder upserts each cell;
  **clearing a cell's subject deletes** that entry (`setEntry` with empty subject).
- **Break periods** (`is_break`) render as a spanning "Break" row with no subject.
- **Teacher clash detection.** A teacher booked by two *different* class/sections
  at the same (day, period) is a clash. `clashesFor(class, section)` reports them so
  the builder can warn — clashes are surfaced, **not hard-blocked** (matches the
  reference's permissive behavior; a school may knowingly want it).
- **Teacher timetable.** The same entries, filtered by teacher, give each teacher
  their personal weekly schedule (`teacherGrid`).

## Divergence from the reference (deliberate)

| Reference | EasySchool | Why |
|---|---|---|
| `sm_class_routines` with `monday`, `monday_start_from`, `monday_room_id`, … (7 days × 3 columns) | Normalized `timetable_entries` (one row per cell) | No wide denormalized table; add days/periods without schema changes. |
| Period slot + break in `sm_class_times` (has an unused `type` exam/class) | `class_periods` (+is_break) | Dropped the unused exam/class discriminator. |
| Time-of-day duplicated onto every routine row | Time lives once on the period; entries reference it | Single source of truth for slot times. |

## Service surface (`TimetableService`)

`periods()` · `days()` · `classGrid(class, section)` · `setEntry(class, section,
day, period, data)` · `clashesFor(class, section)` · `teacherGrid(teacher)`.
