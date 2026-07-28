@extends('layouts.admin')
@section('title', 'Wallet transactions')

@section('content')
<div class="page-head"><h1>Wallet transactions</h1></div>

<div class="card">
    <h3 style="margin-top:0">Deposit / Withdraw</h3>
    <form method="POST" action="{{ route('wallet.transactions.store') }}">
        @csrf
        <div class="grid">
            <div><label>Wallet</label>
                <select name="wallet_id" required>
                    <option value="">—</option>
                    @foreach($wallets as $w)
                        <option value="{{ $w->id }}">{{ optional($w->user)->name }} — Balance: {{ number_format($w->balance, 2) }}</option>
                    @endforeach
                </select></div>
            <div><label>Type</label>
                <select name="type" required>
                    <option value="credit">Deposit</option>
                    <option value="debit">Withdraw</option>
                </select></div>
            <div><label>{{ __('ui.amount') }}</label><input type="number" name="amount" min="0.01" step="0.01" required></div>
            <div><label>Date</label><input type="date" name="transacted_on" value="{{ now()->toDateString() }}" required></div>
            <div><label>Note</label><input type="text" name="note"></div>
            <div style="display:flex;align-items:flex-end"><button class="btn">Post</button></div>
        </div>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0">Recent transactions</h3>
    @if($recent->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Date</th><th>{{ __('ui.name') }}</th><th>Type</th><th>{{ __('ui.amount') }}</th><th>Balance after</th></tr></thead>
        <tbody>
        @foreach($recent as $t)
            <tr>
                <td>{{ optional($t->transacted_on)->format('d M Y') }}</td>
                <td>{{ optional(optional($t->wallet)->user)->name }}</td>
                <td>
                    @if($t->type === 'credit')
                        <span class="badge" style="color:var(--success)">Deposit</span>
                    @else
                        <span class="badge" style="color:var(--danger)">Withdraw</span>
                    @endif
                </td>
                <td>{{ number_format($t->amount, 2) }}</td>
                <td><strong>{{ number_format($t->balance_after, 2) }}</strong></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
