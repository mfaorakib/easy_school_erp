@extends('layouts.admin')
@section('title', 'Summary')

@section('content')
<div class="page-head"><h1>{{ __('ui.summary') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('accounting.summary') }}">
        <div class="grid">
            <div><label>Date ({{ __('ui.from_account') }})</label>
                <input name="from" type="date" value="{{ $from }}"></div>
            <div><label>Date ({{ __('ui.to_account') }})</label>
                <input name="to" type="date" value="{{ $to }}"></div>
            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">{{ __('ui.generate') }}</button>
            </div>
        </div>
    </form>
</div>

<div class="grid">
    <div class="card">
        <label>{{ __('ui.total_income') }}</label>
        <h1>{{ number_format($data['income'], 2) }}</h1>
    </div>
    <div class="card">
        <label>{{ __('ui.total_expense') }}</label>
        <h1>{{ number_format($data['expense'], 2) }}</h1>
    </div>
    <div class="card">
        <label>{{ __('ui.net_balance') }}</label>
        <h1><span class="badge">{{ number_format($data['net'], 2) }}</span></h1>
    </div>
</div>

<div class="grid">
    <div class="card">
        <div class="page-head"><h1>{{ __('ui.income') }} — {{ __('ui.head') }}</h1></div>
        @if(empty($data['income_by_head']))<div class="empty">{{ __('ui.no_records') }}</div>@else
        <div class="overflow-x-auto">
        <table>
            <thead><tr><th>{{ __('ui.head') }}</th><th>{{ __('ui.amount') }}</th></tr></thead>
            <tbody>
            @foreach($data['income_by_head'] as $headName => $amount)
                <tr>
                    <td>{{ $headName }}</td>
                    <td>{{ number_format($amount, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>
    <div class="card">
        <div class="page-head"><h1>{{ __('ui.expense') }} — {{ __('ui.head') }}</h1></div>
        @if(empty($data['expense_by_head']))<div class="empty">{{ __('ui.no_records') }}</div>@else
        <div class="overflow-x-auto">
        <table>
            <thead><tr><th>{{ __('ui.head') }}</th><th>{{ __('ui.amount') }}</th></tr></thead>
            <tbody>
            @foreach($data['expense_by_head'] as $headName => $amount)
                <tr>
                    <td>{{ $headName }}</td>
                    <td>{{ number_format($amount, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>
</div>
@endsection
