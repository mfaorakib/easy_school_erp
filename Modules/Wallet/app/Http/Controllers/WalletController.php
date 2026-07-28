<?php

namespace Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Services\WalletService;

class WalletController extends Controller
{
    public function __construct(private WalletService $service) {}

    public function index()
    {
        $wallets = Wallet::with('user')->get();
        $usersWithout = User::where('is_active', true)
            ->whereNotIn('id', Wallet::pluck('user_id'))
            ->orderBy('name')
            ->get();

        return view('wallet::wallets.index', compact('wallets', 'usersWithout'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id', 'unique:wallets,user_id'],
        ]);

        $this->service->ensureWallet($request->integer('user_id'));

        return redirect()->route('wallet.wallets.index')->with('status', 'Wallet opened.');
    }
}
