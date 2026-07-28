@extends('layouts.admin')
@section('title', 'Wallets')

@section('content')
<div class="page-head"><h1>Wallets</h1></div>

<div class="card">
    <h3 style="margin-top:0">Open wallet</h3>
    <form method="POST" action="{{ route('wallet.wallets.store') }}">
        @csrf
        <div class="grid">
            <div style="grid-column:1/-1"><label>User</label>
                <select name="user_id" required>
                    <option value="">—</option>
                    @foreach($usersWithout as $u)<option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email ?? $u->username }})</option>@endforeach
                </select>
            </div>
            <div style="display:flex;align-items:flex-end"><button class="btn">Open wallet</button></div>
        </div>
    </form>
</div>

<div class="card">
    @if($wallets->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>Balance</th><th>Status</th></tr></thead>
        <tbody>
        @foreach($wallets as $w)
            <tr>
                <td><strong>{{ optional($w->user)->name }}</strong></td>
                <td><span class="badge">{{ number_format($w->balance, 2) }}</span></td>
                <td><span class="badge">{{ $w->is_active ? 'Active' : 'Inactive' }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
