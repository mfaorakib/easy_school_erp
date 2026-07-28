<?php

namespace Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Models\WalletTransaction;
use Modules\Wallet\Services\WalletService;
use RuntimeException;

class TransactionController extends Controller
{
    public function __construct(private readonly WalletService $service) {}

    public function index()
    {
        $wallets = Wallet::with('user')->where('is_active', true)->get();
        $recent  = WalletTransaction::with('wallet.user')->latest('id')->limit(20)->get();

        return view('wallet::transactions.index', compact('wallets', 'recent'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'wallet_id'     => ['required', 'exists:wallets,id'],
            'type'          => ['required', 'in:credit,debit'],
            'amount'        => ['required', 'numeric', 'min:0.01'],
            'note'          => ['nullable', 'string', 'max:255'],
            'transacted_on' => ['required', 'date'],
        ]);

        $wallet = Wallet::findOrFail($data['wallet_id']);

        try {
            $data['type'] === 'credit'
                ? $this->service->deposit($wallet, (float) $data['amount'], $data['note'] ?? null, $data['transacted_on'])
                : $this->service->withdraw($wallet, (float) $data['amount'], $data['note'] ?? null, $data['transacted_on']);
        } catch (RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()
            ->route('wallet.transactions.index')
            ->with('status', 'Transaction posted.');
    }
}
