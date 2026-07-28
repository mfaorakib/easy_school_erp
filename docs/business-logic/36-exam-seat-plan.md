# Exam Seat Plan — Business-Logic Spec

> Auto-generates and prints an exam's room-by-room seating chart from the
> students already scheduled for it — no manual seat-by-seat data entry.

## Entities & tables

| # | Table | Model | Purpose |
|---|---|---|---|
| 1 | `exam_seat_plans` | `ExamSeatPlan` | One plan per exam (unique on `exam_id`). Holds the generation settings: `seats_per_bench`, `mix_classes`. |
| 2 | `exam_seat_plan_rooms` | `ExamSeatPlanRoom` | Which `Timetable\Classroom`s the plan uses, and their fill order (`position`). |
| 3 | `exam_seat_assignments` | `ExamSeatAssignment` | One student's seat: room + `bench_no` + `seat_no`, plus a **snapshot** of `roll_no`/`class_id`/`section_id` at generation time. |

## Business rules

- **One plan per exam, regeneration replaces it.** `SeatPlanService::generate()`
  deletes the existing plan's rooms/assignments and rebuilds them —
  verified: generating twice with different settings never leaves a second
  plan or orphaned rows (`ExamSeatPlan::count()` stays 1 per exam).
- **Eligible students come from the exam's own schedule.** Every distinct
  `class_id`/`section_id` pair with an `ExamSchedule` row for the exam
  contributes its live (`StudentRecord::live()`) students, ordered by roll
  number — no separate "who's taking this exam" list to maintain.
- **Anti-cheating seat mixing is optional but on by default.**
  `mix_classes = true` interleaves the class/section groups round-robin
  (one student from each group in turn) so adjacent seats belong to
  different classes; `false` seats each class/section as one contiguous
  block. Deterministic either way — same inputs always produce the same
  order.
- **Rooms fill in the given order, to capacity.** `seats_per_bench` (1 or 2)
  controls how many students share a bench — `bench_no = floor(seat/seats_per_bench)+1`,
  `seat_no` cycles within it. If total room capacity is short, the excess
  students are simply left unseated and the generate action reports the
  count (`"N could not be seated — add more rooms or capacity"`) rather than
  failing or silently dropping them.
- **Historically accurate, not live-joined.** `roll_no`/`class_id`/`section_id`
  are copied onto the assignment at generation time, so a printed/pinned
  seat plan stays correct even if a student is later promoted or re-rolled.

## Screens

**Admin** (`Examination` module, `/exam/seat-plan`): pick an exam + rooms +
settings → generate → per-room breakdown with print links → clear/regenerate.
**Printing** (`Printing` module, `/printing/seat-plan`): a picker plus two
printable, admin-layout-free views — one room's chart, or every room in the
plan (paginated per room for printing) — both gracefully show a "not
generated yet" message rather than erroring when no plan exists.

## Service surface

`Examination\Services\SeatPlanService`: `generate()` · `assignmentsForRoom()` ·
`assignmentForStudent()`.
`Printing\Services\PrintService` (extended): `seatPlan(examId, ?classroomId)`
— read-only, reuses `SeatPlanService::assignmentsForRoom()` under the hood so
the printable chart and the admin view can never drift apart.
