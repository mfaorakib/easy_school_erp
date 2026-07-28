<?php

namespace Modules\Wallet\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Models\WalletTransaction;
use RuntimeException;

/**
 * Wallet money movements. Every deposit/withdraw updates the wallet balance and
 * writes a ledger row snapshotting the resulting balance. Debits are guarded
 * against insufficient funds so the balance can never go negative.
 */
class WalletService
{
    public function ensureWallet(int $userId): Wallet
    {
        return Wallet::firstOrCreate(['user_id' => $userId], ['balance' => 0, 'is_active' => true]);
    }

    public function deposit(Wallet $wallet, float $amount, ?string $note = null, ?string $date = null): WalletTransaction
    {
        return $this->post($wallet, 'credit', $amount, $note, $date);
    }

    public function withdraw(Wallet $wallet, float $amount, ?string $note = null, ?string $date = null): WalletTransaction
    {
        return $this->post($wallet, 'debit', $amount, $note, $date);
    }

    private function post(Wallet $wallet, string $type, float $amount, ?string $note, ?string $date): WalletTransaction
    {
        if ($amount <= 0) {
            throw new RuntimeException('Amount must be greater than zero.');
        }

        return DB::transaction(function () use ($wallet, $type, $amount, $note, $date) {
            // Lock the row so concurrent postings can't corrupt the balance.
            $fresh = Wallet::whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            if ($type === 'debit' && (float) $fresh->balance < $amount) {
                throw new RuntimeException("Insufficient balance (available: {$fresh->balance}).");
            }

            $fresh->balance = (float) $fresh->balance + ($type === 'credit' ? $amount : -$amount);
            $fresh->save();

            return $fresh->transactions()->create([
                'type'          => $type,
                'amount'        => $amount,
                'balance_after' => $fresh->balance,
                'note'          => $note,
                'transacted_on' => $date ?: now()->toDateString(),
                'created_by'    => Auth::id(),
            ]);
        });
    }
}
