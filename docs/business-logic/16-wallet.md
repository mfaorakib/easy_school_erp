# 16 - Wallet

Business-logic documentation reverse-engineered from the reference system (a Laravel
school-management platform). The reference ships wallet as a standalone module. This
document describes its behaviour precisely and then gives a clean single-school rebuild
recommendation.

---

## 1. Entities & Tables

### 1.1 Where the balance lives

There is **no `wallets` table** in the reference system. The running balance is a single
column on the user record:

- `users.wallet_balance` — `float`, `DEFAULT 0`.
  Added by the wallet module's install migration (`create_wm__wallet_settings_table`),
  which also registers a system payment method named `Wallet` per school.

The balance is mutated **imperatively** in code (`$user->wallet_balance += / -= amount;
$user->update();`). It is the single source of truth for "how much can this user spend".

### 1.2 The ledger table — `wallet_transactions`

Every deposit / refund / spend attempt is recorded as a row. This is a **request + ledger**
table (rows exist in `pending` state before they affect the balance).

| Column           | Type / Default              | Meaning |
|------------------|-----------------------------|---------|
| `id`             | bigint PK                   | |
| `amount`         | double, null                | Transaction amount |
| `payment_method` | varchar, null               | Cash / Bank / Cheque / Wallet / Stripe / RazorPay / etc. |
| `user_id`        | int unsigned, FK users.id (cascade) | Owner of the wallet |
| `bank_id`        | int, null                   | Bank account (when method = Bank) |
| `note`           | varchar, null               | Free text note |
| `type`           | varchar, null               | `diposit`, `refund`, `expense`, `fees_refund` (sic — "diposit" is misspelled in source) |
| `file`           | text, null                  | Uploaded proof (cheque/bank slip) |
| `reject_note`    | text, null                  | Reason if rejected |
| `expense`        | double, null                | Service/expense charge |
| `status`         | varchar, `DEFAULT 'pending'`| `pending`, `approve`, `reject` (also displayed: `refund`) |
| `created_by`     | int, null                   | |
| `academic_id`    | int, `DEFAULT 1`            | Multi-tenant academic year scope |
| `school_id`      | int, `DEFAULT 1`            | Multi-tenant school scope |
| `created_at` / `updated_at` | timestamps       | Date recorded / actioned |

**Important gap:** there is **no `balance_after` column**. The ledger records only the
delta amount, never a snapshot of the resulting balance. The running balance exists solely
on `users.wallet_balance`, so the ledger and the balance can drift apart with no way to
reconcile after the fact.

Models:
- `Modules\Wallet\Entities\WalletTransaction` — ledger row; `userName()` belongsTo `User`.
- `Modules\Wallet\Entities\Wm_WalletSetting` — effectively empty settings shell.

Type semantics:
- `diposit` — credit (adds funds).
- `expense` — debit (fees paid from wallet).
- `refund` — user requests their remaining wallet balance back (debits wallet on approve).
- `fees_refund` — a fee refund credited back into the wallet.

---

## 2. Deposit (Add Funds) Flow

Deposits are recorded first as a `pending` ledger row, then credited to the balance either
by admin approval (offline methods) or automatically on gateway success (online methods).

### 2.1 Offline methods (Bank / Cheque) — needs approval

Web: `WalletController@addWalletAmount`; API: `Student\Payment\WalletController@addWalletAmount`.

1. Student submits amount + method + optional proof file (bank/cheque).
2. A `WalletTransaction` is created: `type = 'diposit'`, `status = 'pending'`,
   `amount`, `payment_method`, `bank_id`, `note`, `file`, `user_id`, `school_id`,
   `academic_id`. **Balance is NOT changed yet.**
3. Notification is sent to the student and to all accountant users (`role_id = 6`).

Approval — `WalletController@walletApprovePayment`:
```
$currentamount = $user->wallet_balance;
$user->wallet_balance = $currentamount + $request->amount;   // credit
$user->update();

$status->status = 'approve';                                  // ledger row approved
$status->update();
```
Rejection — `walletRejectPayment`: sets `status = 'reject'` + `reject_note`; balance
untouched.

### 2.2 Online gateways (Stripe, RazorPay, SslCommerz, CcAveune, ToyyibPay, ...)

1. A `WalletTransaction` `type = 'diposit'` is created up front (used as the gateway
   invoice/order reference).
2. The gateway `handle($data)` redirects the user to pay.
3. On successful callback (e.g. `StripePayment`, and `PaymentHandlerController@handlePayment`
   with `type = 'walletAddBallence'`) the transaction is set `status = 'approve'` and the
   balance is credited **immediately, no admin step**:
   ```
   $currentBalance = $user->wallet_balance;
   $user->wallet_balance = $currentBalance + $data['amount'];
   $user->update();
   ```
   A `wallet_approve` mail is sent.

### 2.3 Fees over-payment auto-deposit

In `StudentFeesController`, when a fee is paid with more than owed (`add_wallet > 0`), the
excess is pushed into the wallet: `wallet_balance += add_wallet` and a `diposit` /
`status = 'approve'` ledger row (`note = 'Fees Extra Payment Add'`) is written.

**Balance math (credit):** `new_balance = old_balance + amount`.

---

## 3. Withdraw / Deduct / Pay Flow

### 3.1 Paying fees from the wallet — `StudentFeesController` (method = `Wallet`)

Insufficient-funds guard (two checks):
```
if ($user->wallet_balance == 0)                       -> 401 "Insufficiant Balance"
if ($user->wallet_balance >= $request->total_paid_amount) {
    $user->wallet_balance -= $request->total_paid_amount;   // debit
    $user->update();
} else {
    -> 401 "Total Amount Is Grater Than Wallet Amount"
}
```
So the wallet **cannot go negative** on a fee payment — the whole amount must be covered.
An `expense`-type ledger row records the spend.

### 3.2 Wallet refund (cash-out remaining balance) — `walletRefundRequest*`

1. `walletRefundRequestStore` creates a `type = 'refund'`, `status = 'pending'` row.
   Only one pending refund per user is allowed (`'You Already Request For Refund'`).
2. `walletApproveRefund` — admin approves:
   ```
   if ($user->wallet_balance < $status->amount) {        // insufficient guard
       Toastr::error('insufficient balance'); return ...;
   }
   $user->wallet_balance = $user->wallet_balance - $status->amount;   // debit
   $user->update();
   $status->status = 'approve';
   ```
3. `walletRejectRefund` — sets `status = 'reject'`; balance untouched.

**Balance math (debit):** `new_balance = old_balance - amount`, only when
`old_balance >= amount` (guarded in every debit path).

---

## 4. Keeping the Running Balance in Sync with the Ledger

The reference keeps them in sync only by **convention**, not by construction:

- `users.wallet_balance` is the authoritative spendable figure and is edited directly.
- `wallet_transactions` is an append-style log, but it stores **no `balance_after`**, so it
  cannot independently reconstruct or verify the balance.
- Balance changes and ledger `status` updates are done as **two separate `->update()`
  calls without a wrapping DB transaction or row lock** in most paths (only a few gateway
  paths use `DB::beginTransaction`). A crash between the two writes, or two concurrent
  requests, can desynchronize the balance from the ledger.
- Only `approve`d rows are meant to have moved the balance, but nothing enforces that the
  sum of approved credits minus approved debits equals `wallet_balance`.

Net: the ledger is descriptive, the balance is imperative, and there is no reconciliation
mechanism. This is the main weakness to fix in a rebuild.

---

## 5. Clean Single-School Rebuild Recommendation

Drop the multi-tenant columns (`school_id`, `academic_id`) and make the ledger
self-verifying by snapshotting the balance on every row.

### 5.1 Tables

```
wallets
  id            PK
  user_id       FK users.id, UNIQUE          -- one wallet per user
  balance       decimal(12,2) NOT NULL DEFAULT 0   -- CHECK (balance >= 0)
  timestamps

wallet_transactions
  id             PK
  wallet_id      FK wallets.id                -- (or user_id directly)
  type           enum('credit','debit')       -- direction, unambiguous
  reason         enum('deposit','fee_payment','refund','fee_refund','adjustment')
  amount         decimal(12,2) NOT NULL CHECK (amount > 0)
  balance_after  decimal(12,2) NOT NULL       -- snapshot AFTER this row applied
  status         enum('pending','approved','rejected') DEFAULT 'pending'
  payment_method varchar NULL
  reference_id   nullable                     -- fee invoice / gateway ref
  note           varchar NULL
  reject_note    varchar NULL
  file           varchar NULL                 -- proof upload
  created_by     FK users.id NULL
  timestamps
```

Use `decimal`, not `float`, for money. Store `balance_after` so the ledger alone can
reconstruct and audit the balance (the reference's biggest missing piece).

### 5.2 Rules

1. **Credit (deposit / fee_refund):** on approval (offline) or gateway success (online):
   inside a DB transaction with `SELECT ... FOR UPDATE` on the wallet row —
   `balance += amount; write row (type=credit, balance_after=balance)`.
2. **Debit (fee_payment / refund):** enforce **non-negative** — reject if
   `amount > balance`. On success, atomically `balance -= amount; write row
   (type=debit, balance_after=balance)`. Never allow the balance below 0.
3. **Atomicity:** always wrap the `wallets` update + `wallet_transactions` insert in one
   DB transaction with a row lock, so balance and ledger can never drift.
4. **Reconciliation invariant:** `wallets.balance` must always equal the `balance_after`
   of the latest approved transaction, and equal `SUM(approved credits) - SUM(approved
   debits)`. This is checkable because `balance_after` is stored.
5. Keep the **pending -> approved/rejected** workflow for offline top-ups; only approved
   rows move the balance. Online gateway deposits auto-approve on verified callback.
6. Drop `school_id` / `academic_id`. Fix the `diposit` misspelling to `deposit`.

---

## Summary — Deposit / Withdraw / Balance Rules (5 lines)

1. Balance lives on `users.wallet_balance` (float); `wallet_transactions` is the ledger
   (types: `diposit`, `refund`, `expense`, `fees_refund`; status: pending/approve/reject) with **no balance_after snapshot**.
2. Deposit: create a `diposit` row (pending) → credit `wallet_balance += amount` on admin
   approval (Bank/Cheque) or automatically on online-gateway success.
3. Withdraw/pay fees: guarded debit — refuse if balance is 0 or `amount > balance`, else
   `wallet_balance -= amount`; the wallet can never go negative.
4. Refund cash-out: pending `refund` row → on approve, guard `balance >= amount` then
   `wallet_balance -= amount`.
5. Balance is mutated imperatively and the ledger has no balance_after, with no wrapping
   transaction in most paths — so rebuild with a `wallets` table, `balance_after`
   snapshots, atomic locked writes, and a non-negative CHECK.
