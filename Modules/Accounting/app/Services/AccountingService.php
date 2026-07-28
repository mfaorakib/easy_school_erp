<?php

namespace Modules\Accounting\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Accounting\Models\AccountHead;
use Modules\Accounting\Models\AccountTransaction;
use Modules\Accounting\Models\AccountTransfer;
use Modules\Accounting\Models\BankAccount;

/**
 * Accounting engine.
 *
 * Balances are always DERIVED, never stored:
 *   bank balance = opening_balance
 *                + Σ income posted to it   + Σ transfers into it
 *                − Σ expense paid from it  − Σ transfers out of it
 * A transaction with no bank account is cash and touches no bank balance.
 */
class AccountingService
{
    /** Record an income or expense entry. */
    public function record(array $data): AccountTransaction
    {
        return AccountTransaction::create([
            'type'             => $data['type'],
            'account_head_id'  => $data['account_head_id'],
            'bank_account_id'  => $data['bank_account_id'] ?? null,
            'title'            => $data['title'],
            'amount'           => $data['amount'],
            'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
            'payment_method'   => $data['payment_method'] ?? 'cash',
            'reference'        => $data['reference'] ?? null,
            'note'             => $data['note'] ?? null,
            'attachment_path'  => $data['attachment_path'] ?? null,
            'created_by'       => Auth::id(),
        ]);
    }

    /**
     * Post an income/expense entry on behalf of ANOTHER module (Fees,
     * Payroll, Inventory) whenever real money actually moves there —
     * idempotent: if this exact (sourceModule, sourceId) pair was already
     * posted, does nothing and returns null, so a source action that runs
     * twice (or a retried request) can never double-count. Posts as cash
     * unless the caller passes $bankAccountId (Payroll's bank-paid payslips
     * do) — most source modules still don't capture which bank a payment
     * landed in, so cash stays the default rather than a hard requirement.
     *
     * The named head is auto-created (once, then reused) under the CURRENT
     * academic year if it doesn't already exist — AccountHead is
     * year-scoped, so a school starting a new year gets its own fresh set
     * the first time each hook fires, exactly like every other year-scoped
     * catalog in this app.
     */
    public function postFromSource(
        string $sourceModule,
        int $sourceId,
        string $type,
        string $headName,
        float $amount,
        string $date,
        ?string $title = null,
        ?int $bankAccountId = null,
    ): ?AccountTransaction {
        if ($amount <= 0) {
            return null;
        }

        // Look across ALL years and include soft-deleted rows: the (source_module,
        // source_id) uniqueness is global (the DB unique index isn't year- or
        // trash-scoped), so a year-scoped/non-trashed exists() could miss an
        // existing row and then 500 on the unique index instead of no-op'ing.
        $alreadyPosted = AccountTransaction::withTrashed()->allYears()
            ->where('source_module', $sourceModule)
            ->where('source_id', $sourceId)
            ->exists();

        if ($alreadyPosted) {
            return null;
        }

        $head = AccountHead::firstOrCreate(['name' => $headName, 'type' => $type], ['type' => $type]);

        return AccountTransaction::create([
            'type'             => $type,
            'account_head_id'  => $head->id,
            'bank_account_id'  => $bankAccountId,
            'title'            => $title ?? $headName,
            'amount'           => $amount,
            'transaction_date' => $date,
            'payment_method'   => $bankAccountId ? 'bank' : 'cash',
            'source_module'    => $sourceModule,
            'source_id'        => $sourceId,
            'created_by'       => Auth::id(),
        ]);
    }

    /**
     * The original auto-posted transaction for a source record, if one exists.
     * Searches across ALL academic years: a refund/return can be processed in
     * a later year than the original was posted in (the source record — a
     * FeePayment, ItemSell, ... — is not year-scoped and route-binds fine), so
     * a year-scoped lookup would return null and the correction would silently
     * never post, leaving the ledger permanently overstated.
     */
    public function transactionFor(string $sourceModule, int $sourceId): ?AccountTransaction
    {
        return AccountTransaction::allYears()
            ->where('source_module', $sourceModule)
            ->where('source_id', $sourceId)
            ->first();
    }

    /**
     * Post a correction against an already-posted transaction (a partial
     * product return, a fee refund, ...) — the original is NEVER edited or
     * deleted, so the ledger always shows exactly what really happened and
     * when. A reduction in income is posted as an EXPENSE (and a reduction in
     * expense as INCOME) under a dedicated "Refunds & Returns" head, so
     * summary()/bankBalance() keep summing by type unchanged — an adjustment
     * is just another real transaction, linked back via parent_transaction_id
     * for traceability. Deliberately does NOT reuse postFromSource()'s
     * (source_module, source_id) idempotency key, since more than one
     * adjustment can legitimately be posted against the same original entry
     * (e.g. two separate partial returns) — parent_transaction_id is the only
     * link, and it carries no uniqueness constraint.
     */
    public function postAdjustment(
        AccountTransaction $original,
        float $amount,
        string $reason,
        ?string $date = null,
        ?int $bankAccountId = null,
    ): ?AccountTransaction {
        if ($amount <= 0) {
            return null;
        }

        $reversedType = $original->type === AccountTransaction::TYPE_INCOME
            ? AccountTransaction::TYPE_EXPENSE
            : AccountTransaction::TYPE_INCOME;

        $headName = $original->type === AccountTransaction::TYPE_INCOME
            ? 'Refunds & Returns (money out)'
            : 'Refunds & Returns (money in)';

        $head = AccountHead::firstOrCreate(['name' => $headName, 'type' => $reversedType], ['type' => $reversedType]);

        return AccountTransaction::create([
            'type'                  => $reversedType,
            'account_head_id'       => $head->id,
            'bank_account_id'       => $bankAccountId,
            'title'                 => 'Adjustment — '.$reason,
            'amount'                => $amount,
            'transaction_date'      => $date ?? now()->toDateString(),
            'payment_method'        => $bankAccountId ? 'bank' : 'cash',
            'is_adjustment'         => true,
            'adjustment_reason'     => $reason,
            'parent_transaction_id' => $original->id,
            'created_by'            => Auth::id(),
        ]);
    }

    /** Move money between two distinct bank accounts. */
    public function transfer(int $fromId, int $toId, float $amount, ?string $purpose = null, ?string $date = null): AccountTransfer
    {
        if ($fromId === $toId) {
            throw ValidationException::withMessages(['to_bank_account_id' => 'Choose two different accounts.']);
        }
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Amount must be greater than zero.']);
        }

        return AccountTransfer::create([
            'from_bank_account_id' => $fromId,
            'to_bank_account_id'   => $toId,
            'amount'               => $amount,
            'purpose'              => $purpose,
            'transfer_date'        => $date ?? now()->toDateString(),
            'created_by'           => Auth::id(),
        ]);
    }

    /** Derived current balance of a bank account. */
    public function bankBalance(BankAccount $account): float
    {
        $income  = (float) $account->transactions()->type(AccountTransaction::TYPE_INCOME)->sum('amount');
        $expense = (float) $account->transactions()->type(AccountTransaction::TYPE_EXPENSE)->sum('amount');
        $in      = (float) AccountTransfer::where('to_bank_account_id', $account->id)->sum('amount');
        $out     = (float) AccountTransfer::where('from_bank_account_id', $account->id)->sum('amount');

        return round((float) $account->opening_balance + $income + $in - $expense - $out, 2);
    }

    /**
     * Income/expense summary for a date range.
     * @return array{income:float, expense:float, net:float, income_by_head:array, expense_by_head:array}
     */
    public function summary(string $from, string $to): array
    {
        $rows = AccountTransaction::with('head')
            ->whereBetween('transaction_date', [$from, $to])
            ->get();

        $income  = (float) $rows->where('type', 'income')->sum('amount');
        $expense = (float) $rows->where('type', 'expense')->sum('amount');

        $byHead = fn (string $type) => $rows->where('type', $type)
            ->groupBy(fn ($t) => optional($t->head)->name ?? '—')
            ->map(fn ($g) => (float) $g->sum('amount'))
            ->sortDesc()
            ->all();

        return [
            'income'          => round($income, 2),
            'expense'         => round($expense, 2),
            'net'             => round($income - $expense, 2),
            'income_by_head'  => $byHead('income'),
            'expense_by_head' => $byHead('expense'),
        ];
    }
}
