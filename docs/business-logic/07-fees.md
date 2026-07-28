# 07 — Fees / Fee Collection Business Logic

Reverse-engineered from the reference system (a legacy Laravel multi-school SMS) so the fee
subsystem can be faithfully rebuilt in the new single-school app. All names below use the
reference system's table/column names for fidelity; the "Clean Rebuild Recommendation" at the
end restates them for the new app.

> Scope note: the reference system actually ships **two** fee engines. This document covers the
> **classic / core engine** (`sm_fees_*` tables + `SmFees*` models), which is the one the task
> targets. A second, newer optional engine (invoice-based `fm_fees_*`) also exists but is out of
> scope except where the classic carry-forward code reads from it.

---

## 1. Entities & Relationships

```
FeesGroup (sm_fees_groups)
   └── has many FeesType (sm_fees_types)         [fees_group_id]
   └── has many FeesMaster (sm_fees_masters)     [fees_group_id]

FeesType (sm_fees_types)
   └── belongs to FeesGroup
   └── referenced by FeesMaster                  [fees_type_id]

FeesMaster (sm_fees_masters)   = "one payable line": group + type + amount + due date
   └── belongs to FeesGroup, FeesType
   └── has many FeesAssign                       [fees_master_id]

FeesAssign (sm_fees_assigns)   = FeesMaster applied to ONE student (in one enrollment record)
   └── belongs to FeesMaster
   └── belongs to Student (student_id) + StudentRecord (record_id)
   └── has many FeesPayment                      [assign_id]
   └── may carry an applied discount             [fees_discount_id, applied_discount]

FeesDiscount (sm_fees_discounts) = a named discount definition (fixed amount)
   └── assigned to students via FeesAssignDiscount (sm_fees_assign_discounts)

FeesAssignDiscount (sm_fees_assign_discounts) = discount granted to a student
   └── belongs to FeesDiscount, Student, StudentRecord

FeesPayment (sm_fees_payments) = one collection transaction (partial or full)
   └── belongs to FeesAssign (assign_id), Student, StudentRecord, FeesType
   └── optionally references FeesDiscount, Bank

FeesCarryForward (sm_fees_carry_forwards) = a student's opening balance rolled from prior year
   └── belongs to Student
```

**Academic-year scoping.** Every classic fee table carries `academic_id` (and `school_id`).
Global Eloquent scopes (`AcademicSchoolScope` / `StatusAcademicSchoolScope`) automatically filter
by the current academic session (`getAcademicId()`) and school. So Groups, Types, Masters,
Assigns, Discounts, AssignDiscounts and Payments are **all academic-year scoped**. Only
`SmFeesCarryForward` has **no global scope** (it is queried across years on purpose so previous
years' dues can be read), though its rows still store `academic_id`.

The extra columns `un_semester_label_id`, `un_academic_id`, `un_subject_id`, `class_id`,
`section_id` exist to support the University and Direct-Fees add-on modes; they are NULL in the
plain school mode.

---

## 2. Table & Field Details

### `sm_fees_groups` — FeesGroup
| column | type | notes |
|---|---|---|
| id | int PK | |
| name | varchar(200) | group name e.g. "Monthly Fee", "Admission" |
| type | varchar(200) | free-text label (rarely used) |
| start_date / end_date | date | optional group validity window |
| due_date | date | optional group-level due date (used only in dues email) |
| description | text | |
| active_status | tinyint (default 1) | |
| created_by / updated_by | int | |
| school_id | int | drop in rebuild |
| academic_id | int | current session |
| un_semester_label_id | int | University mode only |

Model: `SmFeesGroup` (fillable: name, description, created_by, active_status, school_id,
un_semester_label_id, un_subject_id, un_academic_id). `boot()` adds `AcademicSchoolScope`.

### `sm_fees_types` — FeesType
| column | type | notes |
|---|---|---|
| id | int PK | |
| name | varchar(230) | e.g. "January", "Tuition" |
| description | text | |
| fees_group_id | int | parent group |
| active_status | tinyint | |
| school_id / academic_id / un_semester_label_id | int | |

Model: `SmFeesType` belongsTo `SmFeesGroup`. Adds `StatusAcademicSchoolScope`.

### `sm_fees_masters` — FeesMaster (the payable line)
| column | type | notes |
|---|---|---|
| id | int PK | |
| date | date | **the due date** for this fee |
| amount | float | payable amount |
| fees_group_id | int | |
| fees_type_id | int | a (group,type) pair is unique — see store logic |
| active_status | tinyint | |
| school_id / academic_id | int | |
| class_id | int | **Direct-Fees mode only** (per-class master) |
| section_id | int | **Direct-Fees mode only** (per-section master) |
| un_semester_label_id | int | University mode only |

Model: `SmFeesMaster` belongsTo FeesType/FeesGroup; hasMany DirectFeesInstallment. Adds
`StatusAcademicSchoolScope`.

**IMPORTANT — there is no fine column on the master.** In the classic engine a FeesMaster stores
only `amount` + `date` (due date). It is **NOT** per class/section in plain school mode (the
master is global to the academic year; it becomes per-student only when *assigned*). `class_id` /
`section_id` are populated only under the optional Direct-Fees mode. Fine is **not** modelled as a
type/rate here — it is typed in manually at collection time (see §4).

### `sm_fees_assigns` — FeesAssign (master → student)
| column | type | notes |
|---|---|---|
| id | int PK | |
| fees_amount | float | **remaining/current payable** for this student — mutated as payments happen |
| applied_discount | float | discount already baked into this assign |
| fees_master_id | int | which master |
| fees_discount_id | int | which discount was applied (if any) |
| student_id | int | student |
| record_id | int | student's enrolment record (class/section/session instance) |
| class_id / section_id | int | snapshot |
| school_id / academic_id | int | |

Model: `SmFeesAssign` (guarded id). belongsTo master; hasMany payments (`assign_id`). Accessor
attributes compute `total_paid`, `total_fine`, `discount_sum`, `apply_discount_sum` by summing
the related payments. Adds `StatusAcademicSchoolScope`.

Note: `fees_amount` is **not** immutable — the store logic writes the *net-of-discount* amount
into it and then decrements it on each payment (see §4). The original gross is always recoverable
as `fees_amount + applied_discount` and via `feesGroupMaster->amount`.

### `sm_fees_discounts` — FeesDiscount (definition)
| column | type | notes |
|---|---|---|
| id | int PK | |
| name | varchar | |
| code | varchar | unique per school |
| type | enum('once','year') | **once** = applies to a single FeesType; **year** = applies to a whole FeesGroup |
| amount | float | **fixed money amount** (NOT a percentage) |
| description | text | |
| active_status, school_id, academic_id | | |

Model: `SmFeesDiscount` (guarded id). Adds `StatusAcademicSchoolScope`. The discount **amount is
a flat currency value**; percentage discounts do not exist in the classic engine (percentage math
appears only in the Direct-Fees installment add-on).

### `sm_fees_assign_discounts` — FeesAssignDiscount (grant to a student)
| column | type | notes |
|---|---|---|
| id | int PK | |
| student_id / record_id | int | |
| fees_discount_id | int | |
| fees_type_id | int | set when discount type = 'once' (holds the fees_master id chosen) |
| fees_group_id | int | set when discount type = 'year' (holds the fees_group id chosen) |
| applied_amount | double (default 0) | how much of the discount was actually consumed |
| unapplied_amount | double | leftover discount not consumed (when discount > fee) |
| school_id / academic_id | | |

### `sm_fees_payments` — FeesPayment (one collection transaction)
| column | type | notes |
|---|---|---|
| id | int PK | |
| amount | float | money collected in this transaction |
| discount_amount | double | discount credited on this transaction (usually the assign discount) |
| fine | double | fine collected in this transaction (manually entered) |
| fine_title | varchar | free-text reason for the fine |
| discount_month | tinyint | legacy monthly-discount marker |
| payment_date | date | |
| payment_mode | varchar(100) | 'cash' / 'cheque' / 'bank' / gateway name |
| note | text | |
| slip | varchar | uploaded bank slip path |
| assign_id | int | the FeesAssign paid against |
| fees_type_id | int | denormalised for reporting/sums |
| fees_discount_id | int | discount used |
| bank_id | int | destination bank account (when mode = bank) |
| student_id / record_id | int | |
| active_status, school_id, academic_id | | |
| direct_fees_installment_assign_id / installment_payment_id / un_fees_installment_id | int | add-on modes only |

Model: `SmFeesPayment` (guarded id) — **no global scope** (queried explicitly with school_id +
active_status). belongsTo FeesType, FeesMaster (via fees_type_id), StudentRecord.

### `sm_fees_carry_forwards` — FeesCarryForward
| column | type | notes |
|---|---|---|
| id | int PK | |
| student_id | int | |
| balance | double | absolute opening balance amount |
| balance_type | varchar | 'due' (student owes) or 'add' (credit in student's favour) |
| notes | varchar (default 'Fees Carry Forward') | |
| due_date | timestamp | when the carried balance is due |
| active_status, school_id, academic_id | | |

Model: `SmFeesCarryForward` — plain model, **no scope**.

---

## 3. Assigning Fees to Students

### 3a. Creating a FeesMaster
`SmFeesMasterController@store`:
1. Resolve `fees_type_id` + `fees_group_id`. (In University/Direct modes the group & type are
   auto-created from a single name; in plain mode the admin picks an existing type, and the
   master inherits `fees_group_id` from that type.)
2. Guard: a `(fees_group_id, fees_type_id)` pair must be unique for the academic year — if a
   master already exists for that pair, abort ("Already fees assigned").
3. Insert `sm_fees_masters` with `date` (due date), `amount`, `academic_id`, `school_id`.
   Direct-Fees mode additionally stamps `class_id`/`section_id` and builds installments.

So a master is a **template line** scoped to the whole academic year; it is not yet tied to any
student.

### 3b. Bulk assign a master (group) to a class/section
`SmFeesMasterController@feesAssignSearch` → `feesAssignStore` → `feesStoreStudentRecord`:
- Admin opens "Assign Fees" for a **FeesGroup**, filters by class (required), and optionally
  section / category / student-group. A datatable lists matching student **records** with a
  checkbox; already-assigned rows are pre-checked.
- On submit, for **every FeesMaster in that group** and every selected student record:
  1. If the student has an existing assign for this master+record **and no payment yet**, the old
     assign is deleted first (lets re-assign refresh the amount). If the box is unchecked, skip
     (effectively un-assign when unpaid).
  2. If an assign already exists (and had payment), skip.
  3. Create `SmFeesAssign`: `student_id`, `record_id`, `fees_master_id`, `fees_amount = master.amount`,
     `academic_id`, `school_id`.
  4. **Auto-apply a pre-existing yearly discount:** if the student has a `FeesAssignDiscount` for
     this group and `fees_amount > applied_amount`, subtract it: `fees_amount -= applied_amount`,
     store `applied_discount` and `fees_discount_id` on the assign.
  5. **Auto-apply carry-forward credit:** if the student has a `SmFeesCarryForward` balance, create
     an immediate `SmFeesPayment` consuming it against this master (pays `min(master.amount,
     balance)`), then reduce the carry-forward `balance` by the consumed amount. This makes prior
     credit flow into the newly assigned fees automatically.
- Notifications are sent to the student and parent ("New fees assigned").

**"Assign all" variant:** if `fees_assign_all == 1`, the same routine runs over the full filtered
student set instead of only the checkboxed rows.

**`direct_fees_assign` setting.** `generalSetting()->direct_fees_assign` (helper `directFees()`)
switches the whole subsystem into an alternate installment-based mode: masters become
class/section-specific with `DirectFeesInstallment` children, and assignment/collection route
through `DirectFeesInstallmentAssign` + `DireFeesInstallmentChildPayment` instead of the plain
assign/payment flow. Payment status there is stored as `active_status` (0 unpaid / 2 partial /
1 fully paid) on the installment-assign row. In the plain (recommended) mode this setting is off.

### 3c. What one assignment row means
One `sm_fees_assigns` row = "this student, in this enrolment record, owes this one master line,
currently `fees_amount` remaining (already net of `applied_discount`)."

---

## 4. Payment / Collection — the core algorithm

Entry point: `SmFeesController@feesPaymentStore` (plain mode). The collection screen
(`SmFeesCollectController@collectFeesStudent`) lists each assign with a computed balance and opens
a "generate/collect" modal per fee.

### 4a. How paid / due are computed
Paid and due are **not stored** on the assign as authoritative fields — they are derived by
summing payments:

```
gross_amount   = fees_master.amount                       (original line amount)
discount       = SUM(sm_fees_payments.discount_amount)    OR the assign.applied_discount
paid           = SUM(sm_fees_payments.amount)             (active_status = 1)
fine           = SUM(sm_fees_payments.fine)
balance / due  = (gross_amount + fine) - (paid + discount)
```

This exact formula is used in `collectFeesStudentApi`:
`balance = (amount + fine) - (paid + discount_amount)`, and in the dues reports:
`paid = discount_amount + amount; due = total_amount - paid` (only counted as due when the
master's due date `date` has already passed — `if (due_date > now) continue;`).

`fees_amount` on the assign is a **running remainder**: `store` decrements it by the paid amount
on each transaction, so it trends toward 0. It is a convenience mirror of the derived balance,
not the source of truth.

### 4b. Partial payments
**Yes — partial payments are fully supported.** Each collection writes a new `sm_fees_payments`
row for whatever `amount` was entered (may be less than the balance). Multiple payment rows
accumulate against one assign. There is no enforced "pay in full."

### 4c. Fine application
- Fine is **entered manually** at collection time (`request->fine` + `request->fine_title`); it is
  **not auto-derived** from the master or from days overdue in the classic engine. There is no
  fine %/fixed configuration on the master.
- It is stored on the payment row (`fine`, `fine_title`) and **added to the payable** in the
  balance formula (`amount + fine`). To keep the assign remainder consistent, `store` also does:
  `fees_assign->fees_amount -= amount;` then, if a fine was charged, `fees_assign->fees_amount +=
  fine;` (fine increases what remains owed until it too is collected).
- The Fine Report (`fineReportSearch`) simply lists payments where `fine != 0` in a date range.
- (The *due-date* field `sm_fees_masters.date` is what reports use to decide whether a line is
  "overdue" and thus fine-eligible; applying the fine is a manual admin action.)

### 4d. Discount application
- Discounts are **fixed money amounts** applied at **assign time**, not at payment time (in the
  classic engine). See `SmFeesDiscountController@feesDiscountAssignStore`:
  - For `type = 'once'`: apply to the single chosen FeesMaster's assign.
  - For `type = 'year'`: loop every master in the chosen FeesGroup and apply to each assign.
  - Per assign: `if (fees_amount >= discount.amount) { discount = discount.amount; payable =
    fees_amount - discount; unapplied = 0 } else { discount = fees_amount; payable = 0; unapplied
    = discount.amount - fees_amount }`. Then `assign.applied_discount += discount;
    assign.fees_discount_id = id; assign.fees_amount = payable;` and the AssignDiscount stores
    `applied_amount` / `unapplied_amount`.
  - Re-assigning a discount first **reverses** any prior application (`fees_amount +=
    applied_discount; applied_discount = null`) before recomputing — idempotent.
- Editing a discount's amount (`update`) re-applies it to all assigns that currently carry it.
- At collection, the modal may still pass `fees_discount_id` + `applied_amount`, which land on the
  payment row as `discount_amount` and count toward `paid` in the balance formula. Percentage
  discounts exist **only** in the Direct-Fees add-on (`amount * rate / 100`), not here.

### 4e. Payment methods
`payment_mode` is free-text mapped to `SmPaymentMethhod` by name (`ucwords`). Cash, Cheque, Bank
plus gateway names. When the resolved method id == 3 (Bank), the system also writes an
`SmBankStatement` (type=1 credit) and increments the bank account's `current_balance`. A bank slip
file can be uploaded (`slip`). A separate `SmFeesBankPaymentController` handles online/bank-gateway
submitted payments (parent-initiated) with an approval step.

### 4f. Side effects of every successful payment (`feesPaymentStore`)
1. Insert `sm_fees_payments` (amount, fine, fine_title, discount_amount, payment_mode, date, note,
   slip, assign_id, fees_type_id, student_id, record_id, academic_id, school_id).
2. Decrement `fees_assign.fees_amount` by amount (+ fine adjustment as in §4c).
3. Insert an income row `SmAddIncome` (name "Fees Collect", links `fees_collection_id`, income
   head from general settings, payment method, bank account) — fees feed the accounting ledger.
4. If bank method: write `SmBankStatement` and bump `SmBankAccount.current_balance`.

### 4g. Payment status (paid / partial / unpaid)
In the plain engine there is **no stored status enum**; status is inferred from the balance:
- `balance <= 0` → fully paid
- `0 < paid` and `balance > 0` → partial
- `paid == 0` → unpaid
(The Direct-Fees / University installment modes *do* store an `active_status`: 0 unpaid, 2 partial,
1 paid on the installment-assign row.)

### 4h. Refunds
There is **no first-class refund entity**. Reversal is by **deleting a payment**
(`feesPaymentDelete`): it adds the amount back to `fees_assign.fees_amount`, deletes the linked
`SmAddIncome`, and deletes the payment. (Direct-Fees mode's `deleteSubPayment` additionally
reverses the bank statement and balance.) So "refund" = delete/adjust the payment row.

---

## 5. Carry Forward (prior-year dues into a new year)

Model `SmFeesCarryForward`; controller `SmFeesCarryForwardController`.

**Setting.** `generalSetting()->carry_forword_due_day` (an integer number of days). On carry
forward, the due date is computed as `today + carry_forword_due_day days`:
```php
$due_days = generalSetting()->carry_forword_due_day;
$due_date = date("Y-m-d", strtotime("+".$due_days.' days'));
```
(A separate `FeesCarryForwardSettings` row also exists — title, `fees_due_days`, default
`payment_gateway` — for the newer invoice engine's carry-forward UI.)

**Flow (`feesForwardSearch` / `feesForwardStore`):**
1. Admin selects a class + section (+ shift) in the current (new) academic year.
2. For each active student record, look up the student's carry-forward row. If none/zero, the
   system computes the **previous year's total due** by reading the prior academic year's invoices
   (`FmFeesInvoice` → sum of `FmFeesInvoiceChield.due_amount`, queried *without* the academic scope
   so the old year is visible). If `totalDue > 0`, it upserts a `SmFeesCarryForward` with
   `balance = totalDue`, `balance_type = 'due'`, `due_date = today + carry_forword_due_day`,
   `academic_id = current`, notes "Previous year due".
3. `feesForwardStore` lets the admin confirm/override the balance per student. A leading `-` means
   the student **owes** (`balance_type = 'due'`); `+` means **credit** (`balance_type = 'add'`).
   The stored `balance` is the absolute value; `due_date` again = today + carry_forword_due_day.
4. `feesCarryForwardStore` (settings-driven variant) additionally writes a `FeesCarryForwardLog`
   audit row (amount, type fees/installment, note, created_by) and recomputes the delta vs the
   student's existing base balance.

**How the balance is actually consumed.** The carry-forward row is a **student-level opening
balance**. When new fees are later assigned (§3b step 5), any `balance_type = 'add'` credit is
automatically drawn down by creating `SmFeesPayment` rows against the new masters until the credit
is exhausted. A `'due'` balance surfaces the student's outstanding amount so it can be collected in
the new year.

---

## 6. Invoice / Receipt Concept

The classic engine has **no persisted invoice table** — a "receipt" is a rendered view/PDF built
on the fly from the assign + its payments:
- `feesPaymentPrint($id,$group)` → PDF of a single payment.
- `feesPaymentInvoicePrint($id,$s_id)` → combined invoice for one or more assigns (`-`-joined ids),
  pulling `InvoiceSetting` (prefix/start number from `FeesInvoice`), the parent, and any unapplied
  discount.
- `FeesInvoice` model holds only invoice-number formatting (`prefix`, `start_form`) per school.

(The newer optional `fm_fees_*` engine *does* have real invoice tables — `fm_fees_invoices`,
`fm_fees_invoice_chields`, `fm_fees_transactions` — with stored per-line `due_amount`, but that is
a separate engine and not the classic flow documented here.)

---

## 7. Clean Rebuild Recommendation (new single-school app)

Drop `school_id` everywhere (single school). **Keep `academic_year_id` scoping** on every table
except the carry-forward table's cross-year reads. Drop the `un_*` (University) and Direct-Fees
`class_id/section_id/installment` columns unless those modes are needed. Model a real payment
status and a real invoice/receipt from the start.

### Suggested schema

```
fee_groups
  id, name, description, active, academic_year_id, timestamps

fee_types
  id, fee_group_id -> fee_groups, name, description, active, academic_year_id, timestamps

fee_masters                      -- one payable line per (group,type) per year
  id, fee_group_id, fee_type_id, amount DECIMAL(12,2),
  due_date DATE,
  fine_type ENUM('none','fixed','percentage') DEFAULT 'none',   -- NEW: make fine first-class
  fine_amount DECIMAL(12,2) DEFAULT 0,                          --      (reference had none)
  academic_year_id, active, timestamps
  UNIQUE(fee_group_id, fee_type_id, academic_year_id)

fee_discounts
  id, name, code UNIQUE, scope ENUM('type','group') ,           -- reference 'once'/'year'
  amount_type ENUM('fixed','percentage') DEFAULT 'fixed',       -- generalise (ref = fixed only)
  amount DECIMAL(12,2), description, active, academic_year_id, timestamps

fee_assignments                  -- master -> student (per enrolment record)
  id, fee_master_id, student_id, enrollment_id,
  gross_amount DECIMAL(12,2),          -- snapshot of master.amount at assign
  discount_amount DECIMAL(12,2) DEFAULT 0,
  net_amount DECIMAL(12,2),            -- gross - discount (payable)
  fee_discount_id NULL,
  status ENUM('unpaid','partial','paid') DEFAULT 'unpaid',   -- persist it, derive on write
  academic_year_id, timestamps
  UNIQUE(fee_master_id, enrollment_id)

fee_assignment_discounts         -- discount grant per student
  id, fee_assignment_id (or student+scope ref), fee_discount_id,
  applied_amount, unapplied_amount, student_id, enrollment_id, academic_year_id, timestamps

fee_payments                     -- one collection transaction (partial allowed)
  id, fee_assignment_id, student_id, enrollment_id,
  amount DECIMAL(12,2),
  discount_amount DECIMAL(12,2) DEFAULT 0,
  fine_amount DECIMAL(12,2) DEFAULT 0, fine_title,
  payment_method ENUM('cash','cheque','bank','online'), bank_account_id NULL, slip,
  payment_date DATE, note, receipt_no,                       -- persist a receipt number
  created_by, academic_year_id, timestamps

fee_carry_forwards               -- student opening balance from prior year (NOT year-scoped read)
  id, student_id, balance DECIMAL(12,2), balance_type ENUM('due','credit'),
  due_date DATE, notes, academic_year_id, timestamps
```

### Behaviour to preserve
1. **Master = year-scoped template**, unique per (group,type); assignment materialises it per
   student/enrolment.
2. **Assignment stores gross, discount, and net**; keep a persisted `status` but always recompute
   it from payments on every write (`paid = SUM(payments.amount)`,
   `due = (net + total_fine) - paid`).
3. **Partial payments**: each collection = one `fee_payments` row; never require full payment.
4. **Balance formula** (authoritative): `due = (net_amount + Σfine) − (Σpaid + Σdiscount_on_payment)`.
   Treat a line as overdue only when `today > due_date`.
5. **Fine**: promote to a real `fine_type`/`fine_amount` on the master so overdue fines can be
   auto-suggested (percentage of amount or fixed), while still allowing a manual override + title
   at collection. (The reference system only supported manual fines.)
6. **Discount**: fixed by default; add optional percentage. Apply at assign time, reversible/
   idempotent on re-assign, tracking `applied` vs `unapplied` when the discount exceeds the fee.
7. **Carry forward**: `due_date = today + carry_forward_due_days` (config). Store `balance` +
   `balance_type` (due/credit). Auto-consume a `credit` balance by generating payments against
   newly assigned fees; surface a `due` balance as outstanding. Keep this table readable across
   years (do not hard-filter by academic year on read).
8. **Accounting hook**: each payment posts an income ledger entry and, for bank methods, a bank
   statement + balance update. Deleting/refunding a payment reverses all three.
9. **Receipts/Invoices**: generate a stored `receipt_no` per payment; render invoice/receipt PDFs
   from assignment + payments (optionally a real invoice table if grouped invoicing is wanted).
```
```
