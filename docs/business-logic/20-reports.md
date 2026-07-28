# Reports — Business-Logic Spec

> The reference system scatters reporting across dozens of controllers under `Report/*` plus
> per-module report routes (`student-report`, `guardian-report`, `fine-report`,
> `transaction-report`, tabulation / progress-card / marksheet, transport / dormitory reports).
> EasySchool consolidates the highest-value ones into a single **read-only** `Reports` module.

## Design

The Reports module **owns no tables**. It is a thin presentation layer over the operational data
already produced by the other modules. All logic lives in one schema-aware `ReportService`; each
controller is a filter → call → render. Every read inherits the source models' **academic-year
global scope**, and the roster is always the **live enrolment** (`student_records.is_promote = 0`),
so promoted/left students never leak into a current-year report.

## Reports delivered

| Report | Filters | Source | Output |
|---|---|---|---|
| **Student List** | class, section | `StudentRecord::live` + `Student`/`Guardian` | roll, name, admission no, class/section, mobile |
| **Guardian Contacts** | class, section | `Student->guardian` | student, guardian/father name, relation, mobile, email |
| **Attendance Summary** | class, section, from–to | `StudentAttendance` (P/L/A/F/H) | per-student tallies + present-rate % |
| **Fees Collection** | from–to, class | `FeePayment` | dated payments (amount/fine/discount/method/receiver) + totals |
| **Fees Due** | class, section | `FeeAssignment` via `FeeService::balance` | per-student net/settled/**due**, grand total (owers only) |
| **Wallet Statement** | from–to | `WalletTransaction` (credit/debit) | dated movements + credit/debit totals |
| **Exam Results** (tabulation) | exam, class, section | `ExamResult` | ranked total/full, GPA, grade, pass/fail |
| **Merit List** | exam, class, section, top-N | `ExamResult` (passers) | merit-ordered top performers |

## Key business rules preserved / clarified

- **Attendance rate** = `(present + late + 0.5·halfDay) / marked` where `marked` excludes holidays
  (a holiday is not an absence). Half-day counts as half presence.
- **Fees due** aggregates *every* assignment of a student through the same `FeeService::balance`
  the collection screen uses (`net = amount − discounts`, `settled = payments + payment-discounts`,
  `due = max(0, net − settled)`), so a report can never disagree with the ledger. Only students
  with a positive due appear.
- **Exam results / merit** read the already-computed `ExamResult` rows (total, GPA band grade,
  pass = no subject failed, rank by total marks) — the report never recomputes, avoiding drift
  from the `ExamResultService` source of truth.
- **Wallet** credit/debit totals come from the immutable `wallet_transactions` ledger, not the
  mutable balance column.

## Not (yet) in this module

Income/expense and payroll reports wait on the **Accounting** / **HR-Payroll** modules; transport
and dormitory allocation lists can be added as thin `ReportService` methods when needed. The
consolidation pattern (one service method + one filter screen) makes each a small future addition.
