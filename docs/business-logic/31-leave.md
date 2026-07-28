# Leave, Evaluation & Shift — Business-Logic Spec

> The reference system's HR extras — leave define/type/request, teacher evaluation
> and shift management — are gathered into one **Leave** module.

## Entities & tables (all academic-year scoped)

| # | Table | Model | Purpose |
|---|---|---|---|
| 1 | `leave_types` | `LeaveType` | A leave category with an annual day quota (`days_allowed`). |
| 2 | `leave_applications` | `LeaveApplication` | A staff leave request: dates, days, reason, status pending→approved/rejected, reviewer + note. |
| 3 | `teacher_evaluations` | `TeacherEvaluation` | A teacher evaluation: JSON `criteria` (name+score/10) → average `total_score`, remarks. |
| 4 | `shifts` | `Shift` | A work shift (name + start/end time). |
| 5 | `staff_shifts` | `StaffShift` | One shift assigned to a staff member (unique per staff). |

## Business rules

- **Leave days** = inclusive date span (`from..to`). An application starts
  `pending` and consumes no quota.
- **Leave balance** = the type's annual quota − days on **approved** applications
  of that type this year. Approving an application is what draws down the balance
  (a rejected/pending one never does). `balanceSheet(staff)` lists allowed/used/
  remaining per type.
- **Approval** flips status to approved/rejected and records the reviewer + note.
- **Teacher evaluation** scores a teacher on any number of criteria (0–10 each);
  `total_score` is the **average** of the scored criteria (empty rows dropped).
- **Shift assignment** is one shift per staff (`updateOrCreate` on staff_id).

## Divergence from the reference (deliberate)

| Reference | EasySchool | Why |
|---|---|---|
| `SmLeaveDefine` per-role day allocation table | `days_allowed` on the leave type + derived balance | Simpler; balance computed from approved history, never drifts. |
| `SmLeaveDeductionInfo` | (not built) | Deduction ties to payroll; can hook later via approved-days. |
| Evaluation with fixed criteria columns | JSON `criteria` (any number, 0–10) | Configurable per evaluation without schema change. |
| Shift as its own module | folded into Leave (HR extras) | one HR-adjacent home. |

## Service surface (`LeaveService`)

`dayCount(from,to)` · `apply(data)` · `review(app, status, note?)` ·
`balance(staffId, type)` · `balanceSheet(staffId)` · `assignShift(staffId, shiftId)`
· `evaluate(data)` (averages criteria).
