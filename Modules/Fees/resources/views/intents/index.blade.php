@extends('layouts.admin')
@section('title', __('ui.confirm_payment'))

@section('content')
<div class="page-head"><h1>{{ __('ui.confirm_payment') }}</h1></div>

<div class="card">
    @if($intents->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
        <tr>
            <th>{{ __('ui.name') }}</th>
            <th>Fee</th>
            <th>{{ __('ui.amount') }}</th>
            <th>Method</th>
            <th>{{ __('ui.transaction_reference') }}</th>
            <th>Started by</th>
            <th>{{ __('ui.date') }}</th>
            <th>{{ __('ui.actions') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($intents as $i)
            <tr>
                <td>{{ $i->assignment->student->full_name ?? '—' }}</td>
                <td>{{ $i->assignment->master->type->name ?? '—' }}</td>
                <td>{{ number_format($i->amount, 2) }}</td>
                <td>{{ $i->method->name }} <span class="badge">{{ $i->method->driver }}</span></td>
                <td>{{ $i->gateway_reference ?? '—' }}</td>
                <td>{{ $i->initiator->name ?? '—' }}</td>
                <td>{{ $i->created_at->format('d M Y, h:i A') }}</td>
                <td>
                    <form method="POST" action="{{ route('fees.intents.confirm', $i) }}" onsubmit="return confirm('{{ __('ui.confirm_payment') }}?')">
                        @csrf
                        <button class="btn btn-sm">{{ __('ui.confirm_payment') }}</button>
                    </form>
                    <form method="POST" action="{{ route('fees.intents.reject', $i) }}" style="margin-top:.3rem">
                        @csrf
                        <input type="text" name="reason" placeholder="{{ __('ui.reason') }}" required style="font-size:.75rem;padding:.25rem .4rem;width:120px">
                        <button class="btn btn-sm btn-danger">{{ __('ui.reject') }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    {{ $intents->links() }}
    @endif
</div>
@endsection
