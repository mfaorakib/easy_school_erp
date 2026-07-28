@extends('layouts.admin')
@section('title', 'Transfers')

@section('content')
<div class="page-head"><h1>{{ __('ui.transfers') }}</h1>
    <a href="{{ route('accounting.transfers.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>

<div class="card">
    @if($transfers->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>Date</th>
            <th>{{ __('ui.from_account') }}</th>
            <th>{{ __('ui.to_account') }}</th>
            <th>{{ __('ui.amount') }}</th>
            <th>{{ __('ui.purpose') }}</th>
        </tr></thead>
        <tbody>
        @foreach($transfers as $t)
            <tr>
                <td>{{ $t->transfer_date?->format('d M Y') }}</td>
                <td>{{ optional($t->fromAccount)->bank_name }}</td>
                <td>{{ optional($t->toAccount)->bank_name }}</td>
                <td>{{ number_format($t->amount, 2) }}</td>
                <td>{{ $t->purpose ?: '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
