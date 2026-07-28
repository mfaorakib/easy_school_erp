# HR Payroll — Business-Logic Spec

> The reference system's payroll spans salary templates, per-staff monthly payroll
> generation (status NG/G/P), earn/deduc line items and payment records.
> EasySchool rebuilds this as clean, snapshot-based payroll.

## Entities & tables (year-scoped where operational)

| # | Table | Model | Purpose |
|---|---|---|---|
| 1 | `salary_templates` | `SalaryTemplate` | A named grade: a basic pay + components. |
| 2 | `salary_components` | `SalaryComponent` | An earning/deduction line of a template. `calc_type` = fixed amount or percent-of-basic. |
| 3 | `staff_salaries` | `StaffSalary` | Assigns one template to a staff member (unique per staff). |
| 4 | `payslips` | `Payslip` | A generated payslip for one staff × one month (`period` = YYYY-MM). Snapshot amounts; status generated/paid. One per (staff, period). |
| 5 | `payslip_items` | `PayslipItem` | The resolved earning/deduction lines captured on a payslip. |

## Business rules

**Template resolution.** A template resolves to:
```
gross = basic + Σ earning components
net   = gross − Σ deduction components
```
A component is either a **fixed** amount or a **percent** of the basic salary
(`resolve(basic)`).

**Generation is a snapshot.** Generating payroll for a month reads each staff
member's assigned template, resolves it, and **writes the numbers + line items onto
the payslip** (the basic pay becomes the first earning line). Later edits to the
template never rewrite already-generated payslips — history is immutable. One
payslip per (staff, period); re-running a month **skips** staff who already have a
payslip (so paid slips are never clobbered). Batch generation returns how many were
newly created.

**Status.** A payslip is `generated` until marked `paid` (records method + date).
The reference's NG/G/P collapses to this two-state model (NG = simply no payslip row
yet).

**Summary.** For a month: payslip count, total gross, total deductions, total net,
and paid vs unpaid net.

## Divergence from the reference (deliberate)

| Reference | EasySchool | Why |
|---|---|---|
| Template stores fixed columns (house_rent, provident_fund, …) + precomputed gross/net | `salary_components` rows (any number, typed, fixed/percent) | Arbitrary earnings/deductions without schema changes; totals derived. |
| `payroll_generates` stores computed totals with template columns duplicated | `payslips` + `payslip_items` snapshot | Line-item history; template edits don't rewrite past slips. |
| Status NG/G/P + separate `payroll_payments` table | `status` generated/paid + method/date on the slip | One clear lifecycle; payment captured inline. |
| Percent handling implicit | explicit `calc_type` fixed/percent per component | Transparent, per-line calculation. |

## Service surface (`PayrollService`)

`templateTotals(template)` · `assignTemplate(staffId, templateId)` ·
`generateFor(staffId, period)` · `generateBatch(period)` · `markPaid(payslip,…)` ·
`summary(period)`.
