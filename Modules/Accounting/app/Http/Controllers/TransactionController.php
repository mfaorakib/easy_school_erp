<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\AccountHead;
use Modules\Accounting\Models\AccountTransaction;
use Modules\Accounting\Models\BankAccount;
use Modules\Accounting\Services\AccountingService;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $type = $this->type($request);
        $from = $request->input('from');
        $to = $request->input('to');

        $transactions = AccountTransaction::type($type)
            ->with(['head', 'bankAccount'])
            ->when($from, fn ($q) => $q->whereDate('transaction_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('transaction_date', '<=', $to))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $total = $transactions->sum('amount');

        return view('accounting::transactions.index', compact('type', 'transactions', 'total', 'from', 'to'));
    }

    public function create(Request $request)
    {
        $type = $this->type($request);

        return view('accounting::transactions.form', $this->formData(
            new AccountTransaction(['type' => $type]),
            $type
        ));
    }

    public function store(Request $request, AccountingService $accounting)
    {
        $data = $this->validated($request);

        $accounting->record($data);

        return redirect()
            ->route('accounting.transactions.index', ['type' => $data['type']])
            ->with('status', ucfirst($data['type']).' recorded.');
    }

    public function edit(AccountTransaction $transaction)
    {
        abort_if($transaction->isAutoPosted(), 403, 'Auto-posted transactions can only be changed at their source (Fees/Payroll/Inventory).');

        return view('accounting::transactions.form', $this->formData(
            $transaction,
            $transaction->type
        ));
    }

    public function update(Request $request, AccountTransaction $transaction)
    {
        abort_if($transaction->isAutoPosted(), 403, 'Auto-posted transactions can only be changed at their source (Fees/Payroll/Inventory).');

        $data = $this->validated($request);

        if (! array_key_exists('attachment_path', $data)) {
            $data['attachment_path'] = $transaction->attachment_path;
        }

        $transaction->update($data);

        return redirect()
            ->route('accounting.transactions.index', ['type' => $data['type']])
            ->with('status', ucfirst($data['type']).' updated.');
    }

    public function destroy(AccountTransaction $transaction)
    {
        abort_if($transaction->isAutoPosted(), 403, 'Auto-posted transactions can only be changed at their source (Fees/Payroll/Inventory).');

        $type = $transaction->type;
        $transaction->delete();

        return redirect()
            ->route('accounting.transactions.index', ['type' => $type])
            ->with('status', ucfirst($type).' deleted.');
    }

    private function type(Request $request): string
    {
        return in_array($request->input('type'), ['income', 'expense'])
            ? $request->input('type')
            : 'income';
    }

    private function formData(AccountTransaction $transaction, string $type): array
    {
        return [
            'transaction' => $transaction,
            'type'        => $type,
            'heads'       => AccountHead::active()->where('type', $type)->orderBy('name')->get(),
            'banks'       => BankAccount::active()->orderBy('bank_name')->get(),
            'methods'     => ['cash', 'bank', 'cheque', 'card', 'mobile'],
        ];
    }

    private function validated(Request $request): array
    {
        $request->validate([
            'type'            => ['required', 'in:income,expense'],
            'account_head_id' => ['required', 'exists:account_heads,id'],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'title'           => ['required', 'string', 'max:255'],
            'amount'          => ['required', 'numeric', 'min:0'],
            'transaction_date' => ['required', 'date'],
            'payment_method'  => ['required', 'string', 'max:30'],
            'reference'       => ['nullable', 'string', 'max:255'],
            'note'            => ['nullable', 'string'],
            'attachment'      => ['nullable', 'file', 'max:4096'],
        ]);

        $data = $request->only('type', 'account_head_id', 'bank_account_id', 'title', 'amount', 'transaction_date', 'payment_method', 'reference', 'note');
        $data['bank_account_id'] = $request->bank_account_id ?: null;

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('accounting', 'public');
        }

        return $data;
    }
}
