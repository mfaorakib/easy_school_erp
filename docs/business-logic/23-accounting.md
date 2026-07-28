# Accounting (Income / Expense / Bank / Transfers) — Business-Logic Spec

> The reference system splits accounting across income_heads, expense_heads,
> chart_of_accounts, add_incomes, add_expenses, bank_accounts, amount_transfers
> and bank_statements. EasySchool consolidates these into a cleaner typed model
> while preserving the same operations.

## Entities & tables (all academic-year scoped)

| # | Table | Model | Purpose |
|---|---|---|---|
| 1 | `account_heads` | `AccountHead` | A category, `type` = income or expense (one typed table replaces the reference's separate income_heads / expense_heads / chart_of_accounts). |
| 2 | `bank_accounts` | `BankAccount` | A bank/cash account. Only `opening_balance` is stored; current balance is derived. |
| 3 | `account_transactions` | `AccountTransaction` | One income or expense entry (`type`), linked to a head, an optional bank account (null = cash), a payment method, amount, date, reference, attachment. Replaces add_incomes + add_expenses. |
| 4 | `account_transfers` | `AccountTransfer` | Money moved between two bank accounts. |

## Business rules

**Derived balances (never stored).** A bank account's current balance is computed:
```
balance = opening_balance
        + Σ income transactions posted to it   + Σ transfers into it
        − Σ expense transactions paid from it   − Σ transfers out of it
```
A transaction with no bank account is **cash** and touches no bank balance. This
mirrors the Wallet/Inventory rebuild principle: money state is derived from an
immutable ledger, never a mutable column that can drift.

**Transactions.** Every entry is either income or expense, always tied to a head of
the matching type. Payment method is a simple string (cash/bank/cheque/card/mobile);
an optional attachment (receipt) stores on the `public` disk.

**Transfers.** `transfer(from, to, amount)` guards against `from == to` and
non-positive amounts (validation error), then records one transfer row that debits
the source and credits the destination in the derived-balance formula above.

**Summary.** For a date range: total income, total expense, net, and per-head
breakdowns (income-by-head, expense-by-head) — the accounting overview.

## Divergence from the reference (deliberate)

| Reference | EasySchool | Why |
|---|---|---|
| Separate income_heads / expense_heads / chart_of_accounts | One `account_heads` with a `type` | Three tables for the same concept collapsed to one typed table. |
| Separate add_incomes / add_expenses (near-identical columns) | One `account_transactions` with a `type` | Half the tables/controllers; a single ledger to sum. |
| `current_balance` stored + mutated on each entry | Derived every read | Can't drift; matches Wallet/Inventory. |
| `payment_method_id` lookup table | `payment_method` string | A 5-value lookup wasn't worth a table/join. |
| `bank_statements`, `bank_payment_slips` | (not built) | Statement is derivable from the ledger; slips are a print concern. |

**Done (Phase 39):** Fees collection, Payroll payslip payment, and Inventory
receive/sell now auto-post into this ledger via `AccountingService::postFromSource()`
— idempotent per `(source_module, source_id)`, always posted as cash, shown with
an "Auto" badge and not editable/deletable directly in Accounting (fix at the
source instead). See `docs/BUILD-LOG.md` Phase 39 for the full design and
verification. Bank-level precision (which bank a fee/payroll/inventory payment
actually landed in) is a possible future refinement — not needed for the
consolidated income/expense picture this closes the gap on.

## Service surface (`AccountingService`)

`record(array)` · `transfer(fromId, toId, amount, purpose?, date?)` ·
`bankBalance(BankAccount)` · `summary(from, to)`.
