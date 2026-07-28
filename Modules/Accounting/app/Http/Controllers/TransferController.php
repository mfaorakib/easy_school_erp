<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\AccountTransfer;
use Modules\Accounting\Models\BankAccount;
use Modules\Accounting\Services\AccountingService;

class TransferController extends Controller
{
    public function index()
    {
        $transfers = AccountTransfer::with(['fromAccount', 'toAccount'])
            ->orderByDesc('transfer_date')
            ->orderByDesc('id')
            ->get();

        return view('accounting::transfers.index', compact('transfers'));
    }

    public function create()
    {
        $accounts = BankAccount::active()->orderBy('bank_name')->get();

        return view('accounting::transfers.create', compact('accounts'));
    }

    public function store(Request $request, AccountingService $accounting)
    {
        $request->validate([
            'from_bank_account_id' => ['required', 'exists:bank_accounts,id'],
            'to_bank_account_id'   => ['required', 'exists:bank_accounts,id', 'different:from_bank_account_id'],
            'amount'               => ['required', 'numeric', 'min:0.01'],
            'purpose'              => ['nullable', 'string', 'max:255'],
            'transfer_date'        => ['required', 'date'],
        ]);

        $accounting->transfer(
            (int) $request->from_bank_account_id,
            (int) $request->to_bank_account_id,
            (float) $request->amount,
            $request->purpose,
            $request->transfer_date
        );

        return redirect()->route('accounting.transfers.index')->with('status', 'Transfer recorded.');
    }
}
