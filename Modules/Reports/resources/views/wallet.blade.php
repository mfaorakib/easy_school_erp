@extends('layouts.admin')
@section('title', __('ui.wallet_report'))

@section('content')
<div class="page-head"><h1>{{ __('ui.wallet_report') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('reports.wallet') }}">
        <div class="grid">
            <div>
                <label>{{ __('ui.from_date') }}</label>
                <input type="date" name="from" value="{{ $from }}">
            </div>
            <div>
                <label>{{ __('ui.to_date') }}</label>
                <input type="date" name="to" value="{{ $to }}">
            </div>
            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">{{ __('ui.generate') }}</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="page-head">
        <div>
            <span class="badge">{{ __('ui.credit') }}: {{ number_format($data['total_credit'], 2) }}</span>
            <span class="badge">{{ __('ui.debit') }}: {{ number_format($data['total_debit'], 2) }}</span>
        </div>
    </div>

    @if($data['transactions']->isEmpty())
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
                <th>Balance After</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data['transactions'] as $t)
            <tr>
                <td>{{ $t->transacted_on?->format('d M Y') }}</td>
                <td>{{ optional(optional($t->wallet)->user)->name ?? '—' }}</td>
                <td>
                    @if($t->type === 'credit')
                        <span class="badge">{{ __('ui.credit') }}</span>
                    @else
                        <span class="badge">{{ __('ui.debit') }}</span>
                    @endif
                </td>
                <td>{{ number_format($t->amount, 2) }}</td>
                <td>{{ number_format($t->balance_after, 2) }}</td>
                <td>{{ $t->note ?: '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
