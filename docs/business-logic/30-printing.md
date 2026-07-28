# Printing — Marksheet / Tabulation / Progress Card / Fee Statement — Spec

> The reference system prints result documents (marksheet, tabulation sheet,
> progress card) via `Report/SmReportController` and fee invoices via the fees
> module + BulkPrint. EasySchool gathers these into a **Printing** module — a
> read-only print centre over existing exam and fee data.

## What it produces (no new tables — all read-only)

| Document | Data | Output |
|---|---|---|
| **Marksheet** | one student × one exam | subject-wise full/pass/obtained/grade/result + total/GPA/grade/position/pass-fail, signatures |
| **Tabulation** | one class/section × one exam | students × subjects grid + total/GPA/grade/rank (landscape) |
| **Progress Card** | one student × all the year's exams | term-by-term GPA/grade + overall GPA |
| **Fee Statement** | one student | fee ledger (per assignment: amount/discount/paid/due/status) + net/paid/due totals |

Each flow: pick a template of recipients (exam+class+section+students, or just
class+section+students), then **Generate opens a print page in a new tab** (browser
Print → PDF). Batch selection prints one document per recipient
(`page-break-after`). A **Print Center hub** links all four plus the Documents
module's ID cards / certificates.

## Data resolution (`PrintService`)

- **marksheet(exam, student):** the student's live class/section → that exam's
  `ExamSchedule` rows (subjects + full/pass marks) → `Mark` per subject (obtained /
  absent) → grade from `GradeScale::forPercentage`; the aggregate from the stored
  `ExamResult` (total/GPA/grade/position/pass).
- **tabulation(exam, class, section):** the live roster × the exam's scheduled
  subjects, filled from `Mark`, ranked from `ExamResult`.
- **progressCard(student):** every `ExamResult` for the student this year → one row
  per term; overall GPA = mean of term GPAs.
- **invoice(student):** `FeeService::studentLedger` (each assignment's derived
  balance) → net/paid/due totals — always agrees with the fees ledger.

School name comes from the Builder `SiteSetting`. Reads never recompute results —
they render the already-computed `ExamResult`/ledger, so a printout can't disagree
with the system.

## Design

- Reuses the Documents module's **print layout** (a screen Print/Back toolbar that
  `@media print` hides). Marksheet/progress/invoice are portrait A4-ish sheets with
  bordered headers, subject tables, summary chips and signatures; tabulation is a
  landscape grid that scrolls on screen and fits the page on print. Theme-neutral
  (documents print on white), RTL/locale-aware.

## Divergence from the reference (deliberate)

| Reference | EasySchool | Why |
|---|---|---|
| Report controllers scattered across areas | one Printing module + hub | one place to print. |
| Marksheet/tabulation recompute or read mixed sources | render stored `ExamResult` + schedules/marks | one source of truth, no drift. |
| Invoice = a single receipt | full **fee statement** (ledger + due) | more useful; ties to the derived-balance ledger. |
