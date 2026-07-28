<?php

namespace Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Models\WalletTransaction;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $walletId = $request->integer('wallet_id') ?: null;

        $wallets = Wallet::with('user')->get();

        $transactions = WalletTransaction::with('wallet.user')
            ->when($walletId, fn ($q) => $q->where('wallet_id', $walletId))
            ->latest('transacted_on')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('wallet::history.index', compact('wallets', 'transactions', 'walletId'));
    }
}
