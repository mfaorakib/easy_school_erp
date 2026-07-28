@extends('layouts.admin')
@section('title', __('ui.wallet_transaction_history'))

@section('content')
<div class="page-head"><h1>{{ __('ui.wallet_transaction_history') }}</h1></div>

<div class="card">
    <h3 style="margin-top:0">Filter transactions</h3>
    <form method="GET" action="{{ route('wallet.history.index') }}">
        <div class="grid">
            <div><label>Wallet</label>
                <select name="wallet_id">
                    <option value="">All wallets</option>
                    @foreach($wallets as $wallet)
                        <option value="{{ $wallet->id }}" @selected($walletId === $wallet->id)>{{ optional($wallet->user)->name ?? '—' }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;align-items:flex-end"><button class="btn">{{ __('ui.load') }}</button></div>
        </div>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0">Transaction history</h3>
    @if($transactions->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>User</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Balance after</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
        @foreach($transactions as $transaction)
            <tr>
                <td>{{ optional($transaction->transacted_on)->format('d M Y') }}</td>
                <td>{{ optional(optional($transaction->wallet)->user)->name ?? '—' }}</td>
                <td>
                    @if($transaction->type === 'credit')
                        <span class="badge" style="color:var(--success)">Deposit</span>
                    @else
                        <span class="badge" style="color:var(--danger)">Withdraw</span>
                    @endif
                </td>
                <td>{{ $transaction->amount }}</td>
                <td>{{ $transaction->balance_after }}</td>
                <td>{{ $transaction->note ?? '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    <div style="margin-top:1rem">{{ $transactions->links() }}</div>
    @endif
</div>
@endsection
