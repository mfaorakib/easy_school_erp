@extends('layouts.admin')
@section('title', $type === 'income' ? __('ui.income') : __('ui.expense'))

@section('content')
<div class="page-head"><h1>{{ $type === 'income' ? __('ui.income') : __('ui.expense') }}</h1>
    <a href="{{ route('accounting.transactions.create', ['type' => $type]) }}" class="btn">+ {{ __('ui.add') }}</a></div>

<div class="card">
    <form method="GET" action="{{ route('accounting.transactions.index') }}">
        <input type="hidden" name="type" value="{{ $type }}">
        <div class="grid">
            <div><label>From</label><input type="date" name="from" value="{{ $from }}"></div>
            <div><label>To</label><input type="date" name="to" value="{{ $to }}"></div>
            <div style="align-self:end"><button class="btn btn-sm" type="submit">{{ __('ui.generate') }}</button></div>
        </div>
    </form>
</div>

<div class="card">
    <p class="badge">{{ $type === 'income' ? __('ui.total_income') : __('ui.total_expense') }}: {{ number_format($total, 2) }}</p>

    @if($transactions->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>Date</th>
            <th>{{ __('ui.head') }}</th>
            <th>Title</th>
            <th>{{ __('ui.amount') }}</th>
            <th>{{ __('ui.payment_method') }}</th>
            <th>Account</th>
            <th>{{ __('ui.actions') }}</th>
        </tr></thead>
        <tbody>
        @foreach($transactions as $t)
            <tr>
                <td>{{ $t->transaction_date?->format('d M Y') }}</td>
                <td>{{ optional($t->head)->name }}</td>
                <td>
                    <strong>{{ $t->title }}</strong>
                    @if($t->isAutoPosted())
                        @php($__sourceLabel = ucfirst(str_replace(['_receive', '_sell'], '', $t->source_module)))
                        <span class="badge" style="background:var(--primary-soft);color:var(--primary)" title="Automatically posted from {{ $__sourceLabel }}">Auto</span>
                    @endif
                </td>
                <td>{{ number_format($t->amount, 2) }}</td>
                <td>{{ $t->payment_method }}</td>
                <td>{{ optional($t->bankAccount)->bank_name ?: __('ui.cash') }}</td>
                <td class="actions">
                    @if($t->isAutoPosted())
                        <span style="color:var(--muted);font-size:.8rem">Auto-posted — edit at the source</span>
                    @else
                        <a href="{{ route('accounting.transactions.edit', $t) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                        <form method="POST" action="{{ route('accounting.transactions.destroy', $t) }}" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')<button class="btn btn-sm btn-danger">{{ __('ui.delete') }}</button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
