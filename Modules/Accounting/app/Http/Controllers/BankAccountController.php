<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\BankAccount;
use Modules\Accounting\Services\AccountingService;

class BankAccountController extends Controller
{
    public function index(AccountingService $accounting)
    {
        $accounts = BankAccount::orderBy('bank_name')->get();
        $balances = $accounts->mapWithKeys(fn ($a) => [$a->id => $accounting->bankBalance($a)]);

        return view('accounting::banks.index', compact('accounts', 'balances'));
    }

    public function create()
    {
        return view('accounting::banks.form', ['account' => new BankAccount]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');

        BankAccount::create($data);

        return redirect()->route('accounting.banks.index')->with('status', 'Bank account saved.');
    }

    public function edit(BankAccount $bankAccount)
    {
        return view('accounting::banks.form', ['account' => $bankAccount]);
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');

        $bankAccount->update($data);

        return redirect()->route('accounting.banks.index')->with('status', 'Bank account saved.');
    }

    public function destroy(BankAccount $bankAccount)
    {
        $inUse = $bankAccount->transactions()->exists()
            || \Modules\Accounting\Models\AccountTransfer::where('from_bank_account_id', $bankAccount->id)
                ->orWhere('to_bank_account_id', $bankAccount->id)
                ->exists();

        if ($inUse) {
            return back()->with('error', 'This bank account has transaction or transfer history — deactivate it instead of deleting.');
        }

        $bankAccount->delete();

        return redirect()->route('accounting.banks.index')->with('status', 'Bank account deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'bank_name'       => ['required', 'string', 'max:255'],
            'account_name'    => ['nullable', 'string', 'max:255'],
            'account_number'  => ['nullable', 'string', 'max:255'],
            'account_type'    => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['required', 'numeric'],
            'note'            => ['nullable', 'string'],
        ]);
    }
}
