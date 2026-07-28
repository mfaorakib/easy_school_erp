# Inventory / Stock — Business Logic

Documents the inventory/stock module of the reference system so it can be faithfully rebuilt for a single-school ERP.

---

## 1. Entities & Tables

The module is a small **item catalog + running-balance stock ledger** built from six core tables plus three child/payment tables.

| Entity | Table | Purpose | Scope in reference |
|--------|-------|---------|--------------------|
| Item Category | `sm_item_categories` | Grouping of items (e.g. "Uniform", "Stationery") | `school_id` + `academic_id` |
| Item | `sm_items` | The stocked product; **holds the running stock balance** | `school_id` + `academic_id` |
| Item Store | `sm_item_stores` | Physical storage location (name + store no.) | `school_id` + `academic_id` |
| Supplier | `sm_suppliers` | Vendor that items are purchased from | `school_id` + `academic_id`, active-status scoped |
| Item Receive (header) | `sm_item_receives` | Stock-IN purchase document (supplier, store, totals, payment) | `school_id` + `academic_id` |
| Item Receive line | `sm_item_receive_children` | Per-item lines of a receive (item, qty, unit price) | `school_id` + `academic_id` |
| Item Issue | `sm_item_issues` | Stock-OUT to a staff/student, with return | `school_id` + `academic_id` |
| Item Sell (header) | `sm_item_sells` | Stock-OUT by sale to student/staff/parent | `school_id` + `academic_id` |
| Item Sell line | `sm_item_sell_children` | Per-item lines of a sell (item, qty, sell price) | `school_id` + `academic_id` |
| Inventory Payment | `sm_inventory_payments` | Follow-up payments against a receive OR sell (due settlement) | `school_id` + `academic_id` |

### Scoping notes
- Every table carries `school_id` (multi-tenant) and `academic_id` (academic-year). For a single-school rebuild, **drop `school_id`** and keep `academic_id` where the reference uses it.
- Global scopes applied by the models:
  - `SmItem`, `SmItemCategory`, `SmItemStore`, `SmItemReceive`, `SmItemReceiveChild`, `SmItemSellChild`, `SmInventoryPayment` → **SchoolScope** (school only).
  - `SmItemIssue` → **AcademicSchoolScope** (school **and** academic year — issues are academic-year scoped).
  - `SmSupplier`, `SmItemSell` → **ActiveStatusSchoolScope** (school + `active_status = 1`).
- Practically: **Item Issue is the one flow explicitly filtered by academic year** at query time; the others filter only by school (with an `academic_id` column still stored for reporting).

### Key columns

**`sm_items`** (the catalog + stock holder)
- `id`, `item_name`, `item_category_id` → category, `description`
- `total_in_stock` (double) — **the running stock balance. This is the single source of truth for current stock.**
- `school_id`, `academic_id`
- Note: **no unit column, no per-store quantity, no reorder level.**

**`sm_item_categories`**: `id`, `category_name`, `school_id`, `academic_id`.

**`sm_item_stores`**: `id`, `store_name`, `store_no`, `description`, `school_id`, `academic_id`. Pure location metadata.

**`sm_suppliers`**: `id`, `company_name`, `company_address`, `contact_person_name`, `contact_person_mobile`, `contact_person_email`, `cotact_person_address` (sic), `description`, `active_status`.

**`sm_item_receives`** (purchase header): `id`, `receive_date`, `reference_no`, `supplier_id`, `store_id`, `grand_total`, `total_quantity`, `total_paid`, `total_due`, `paid_status` (`P`/`PP`/`U`/`R`), `payment_method`, `account_id` (bank), `expense_head_id`, `school_id`, `academic_id`.

**`sm_item_receive_children`** (purchase line): `id`, `item_receive_id`, `item_id`, `unit_price`, `quantity`, `sub_total`. **No `store_id` on the line** — store lives only on the header.

**`sm_item_issues`** (issue to a person): `id`, `role_id`, `issue_to` (user/staff/student id), `issue_by`, `item_category_id`, `item_id`, `issue_date`, `due_date`, `quantity`, `issue_status` (`I` = issued, `R` = returned), `note`, `school_id`, `academic_id`. **No `store_id`, single item per issue row.**

**`sm_item_sells`** (sale header): `id`, `role_id`, `student_staff_id` (buyer), `sell_date`, `reference_no`, `grand_total`, `total_quantity`, `total_paid`, `total_due`, `paid_status`, `income_head_id`, `account_id`, `payment_method`, `description`. **No `store_id`.**

**`sm_item_sell_children`** (sale line): `id`, `item_sell_id`, `item_id`, `sell_price`, `quantity`, `sub_total`.

**`sm_inventory_payments`**: `id`, `item_receive_sell_id` (points at a receive OR a sell), `payment_date`, `amount`, `reference_no`, `payment_type` (`R` = receive, `S` = sell), `payment_method`, `notes`.

---

## 2. Stock Model — RUNNING BALANCE (not derived)

**Current stock is a running balance column, `sm_items.total_in_stock`.** It is **not** computed on the fly from `Σreceives − Σissues − Σsells`. Every stock movement mutates this single column inside the same request that writes the movement row.

Key properties:
- **Per-item only, NOT per-item-per-store.** Although a receive records a `store_id` on its header, the stock increment is applied to `sm_items.total_in_stock` globally for the item. Stores are informational; the system does **not** track how much of an item sits in each store.
- A new item is created with `total_in_stock = 0` (see `SmItemController::store`).
- There is **no stock-movement ledger table**; the receive/issue/sell rows collectively serve as history, but the authoritative quantity is the mutable counter on the item.

Movement rules (all mutate `total_in_stock` directly):

| Action | Effect on `total_in_stock` |
|--------|----------------------------|
| Receive line saved | `+= quantity` |
| Receive edited | old lines first reversed (`-= old qty` each), then new lines `+= qty` |
| Receive line deleted / receive deleted | `-= quantity` |
| Receive cancelled (`paid_status = 'R'`) | `-= quantity` for each child line |
| Issue saved | `-= quantity` |
| Issue returned (`issue_status = 'R'`) | `+= quantity` |
| Sell line saved | `-= quantity` |
| Sell edited | lines deleted and re-created, each new line `-= qty` (note: reference does **not** re-add old sold qty on edit — a known asymmetry vs. receive edit) |
| Sell cancelled (`paid_status = 'S'`) | `+= quantity` for each child line |

Because the balance is stored and mutated in application code (no DB trigger, no transaction wrapper in the reference), correctness depends entirely on these controller code paths.

---

## 3. Receive Flow (Stock-IN)

Controller: `SmItemReceiveController::saveItemReceiveData`.

1. Create a **receive header** (`sm_item_receives`) with `supplier_id`, `store_id`, `receive_date`, `reference_no`, totals (`grand_total`, `total_quantity`, `total_paid`, `total_due`), and derived `paid_status`:
   - `total_due == 0` → `P` (paid); `subtotal == due` (nothing paid) → `U` (unpaid); else → `PP` (partially paid).
2. Record the purchase as an **expense** (`sm_add_expenses`, name = "Item Receive") for the paid amount.
3. If a real bank `payment_method` is used, write a **bank statement** debit (`type = 0`) and decrement `sm_bank_accounts.current_balance` by `total_paid`.
4. For each line item, create a `sm_item_receive_children` row (item, `unit_price`, `quantity`, `sub_total`) and then:
   ```php
   $item->total_in_stock += $line->quantity;   // stock increment
   $item->update();
   ```
5. Later payments against the due amount go through `saveItemReceivePayment` → inserts a `sm_inventory_payments` (`payment_type = 'R'`), decrements `total_due`, increments `total_paid`, re-derives `paid_status`, and posts another expense + bank debit. `deleteReceivePayment` reverses all of that.
6. **Cancel** (`cancelItemReceive`) sets `paid_status = 'R'`, deletes the expense, refunds the bank, and subtracts each line quantity back out of stock.

**Net stock effect of a receive: `total_in_stock += Σ line.quantity`.**

---

## 4. Issue Flow (Stock-OUT to a person, with return)

Controller: `SmItemSellController::saveItemIssueData` (issue endpoints live in the Sell controller).

1. **Stock guard first** — reject if requested qty exceeds current stock:
   ```php
   if ($item->total_in_stock < $request->quantity) {
       // "Quantity can not be greater than stock" → abort
   }
   ```
2. Resolve the recipient: `role_id == 2` → a student id; otherwise a staff id → stored in `issue_to`. `issue_by` = current user.
3. Insert a `sm_item_issues` row: `item_category_id`, `item_id`, `issue_date`, `due_date`, `quantity`, `note`, `issue_status = 'I'` (issued).
4. Decrement stock:
   ```php
   $item->total_in_stock -= $request->quantity;
   $item->update();
   ```
5. **Return** (`returnItem`): looks up the issue, adds its quantity back to `total_in_stock`, and flips `issue_status` to `R` (returned). No partial returns — the full issued quantity is returned in one action.

Notes: an issue is a **single item, single quantity** (no line children). There is no monetary/payment side to an issue — it is a pure stock loan. Issues are the academic-year-scoped entity.

---

## 5. Sell Flow (Stock-OUT by sale) — concise

Controller: `SmItemSellController::saveItemSellData`. Sells an item to a student / staff / parent (chosen by `role_id` + `student_staff_id`).

- Header `sm_item_sells` + line rows `sm_item_sell_children` (mirrors the receive structure but with `sell_price` and an **income** side).
- Guard: `totalPaid` may not exceed subtotal. `paid_status` derived exactly like receives (`P`/`PP`/`U`).
- Posts an **income** row (`sm_add_incomes`, "Item Sell"), a bank **credit** (`type = 1`, `current_balance += total_paid`), and per-line `total_in_stock -= quantity`.
- Due settlement via `saveItemSellPayment` → `sm_inventory_payments` with `payment_type = 'S'`. Cancel (`cancelItemSell`, status `S`) adds sold quantities back to stock and reverses income/bank.
- **Unlike issue, the sell flow does NOT pre-check stock**, so it can drive `total_in_stock` negative.

---

## 6. Clean Single-School Rebuild Recommendation

Keep the running-balance model — it is simple and matches the reference — but tighten the gaps.

**Tables** (drop `school_id` everywhere; keep `academic_id` where noted):

- `item_categories` — `id`, `name`, `academic_id`, timestamps.
- `suppliers` — `id`, `company_name`, `company_address`, `contact_person_name`, `contact_person_mobile`, `contact_person_email`, `contact_person_address`, `description`, `active_status`.
- `item_stores` — `id`, `store_name`, `store_no`, `description`.
- `items` — `id`, `name`, `item_category_id`, `unit` *(add — reference lacks it)*, `description`, `total_in_stock` (decimal, default 0), `reorder_level` *(optional, add for low-stock alerts)*, `academic_id`.
- `item_receives` (+ `item_receive_children`) — header: `receive_date`, `reference_no`, `supplier_id`, `store_id`, totals, `paid_status`, `payment_method`, `account_id`, `expense_head_id`, `academic_id`. Lines: `item_id`, `unit_price`, `quantity`, `sub_total`.
- `item_issues` — `role_id`, `issue_to`, `issue_by`, `item_category_id`, `item_id`, `issue_date`, `due_date`, `quantity`, `issue_status` (`I`/`R`), `note`, `academic_id` (**keep academic-year scoping** — reference scopes issues by year).
- `item_sells` (+ `item_sell_children`) and `inventory_payments` — carry over if selling is in scope; otherwise omit.

**Stock model:**
- **Keep the running `items.total_in_stock` balance**, mutated on every receive/issue/sell/return/cancel exactly as above. It is O(1) to read and matches the reference.
- **Improvements over the reference:**
  - Wrap each movement (write the movement row + adjust `total_in_stock`) in a **DB transaction** so the counter can never drift from history.
  - Apply the **stock-availability guard to the sell flow too** (the reference only guards issues), so stock cannot go negative.
  - Optionally keep a derived cross-check: `total_in_stock` should equal `Σ receive_qty − Σ issued(not returned) − Σ sold(not cancelled)`; expose this as a reconciliation report.
- **Per-store stock:** the reference does **not** track quantity per store (store is header metadata only). If per-store balances are required, add an `item_store_id` to receive/issue lines and hold stock in an `item_stock (item_id, store_id, quantity)` table instead of a single column. Otherwise keep the simpler global-per-item counter.

---

### File references (reference system, read-only)
- Models: `app/SmItem.php`, `app/SmItemCategory.php`, `app/SmItemStore.php`, `app/SmSupplier.php`, `app/SmItemReceive.php`, `app/SmItemReceiveChild.php`, `app/SmItemIssue.php`, `app/SmItemSell.php`, `app/SmItemSellChild.php`, `app/SmInventoryPayment.php`
- Controllers: `app/Http/Controllers/Admin/Inventory/SmItemController.php`, `SmItemReceiveController.php`, `SmItemSellController.php` (holds save/edit/cancel for sell **and** the issue + return actions), `SmItemStoreController.php`, `SmItemCategoryController.php`, `SmSupplierController.php`
- Schema: `sm_items`, `sm_item_categories`, `sm_item_stores`, `sm_suppliers`, `sm_item_receives`, `sm_item_receive_children`, `sm_item_issues`, `sm_item_sells`, `sm_item_sell_children`, `sm_inventory_payments`
