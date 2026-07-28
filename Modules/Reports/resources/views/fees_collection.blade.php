@extends('layouts.admin')
@section('title', __('ui.fees_collection_report'))

@section('content')
<div class="page-head"><h1>{{ __('ui.fees_collection_report') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('reports.fees.collection') }}">
        <div class="grid">
            <div>
                <label>{{ __('ui.from_date') }}</label>
                <input type="date" name="from" value="{{ $from }}">
            </div>
            <div>
                <label>{{ __('ui.to_date') }}</label>
                <input type="date" name="to" value="{{ $to }}">
            </div>
            <div>
                <label>{{ __('ui.classes') }}</label>
                <select name="class_id">
                    <option value="">—</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
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
            <span class="badge">{{ __('ui.collected') }}: {{ number_format($data['total_amount'], 2) }}</span>
            <span class="badge">Fine: {{ number_format($data['total_fine'], 2) }}</span>
            <span class="badge">Discount: {{ number_format($data['total_discount'], 2) }}</span>
        </div>
    </div>

    @if($data['payments']->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Student</th>
                <th>Fee Type</th>
                <th>Amount</th>
                <th>Fine</th>
                <th>Discount</th>
                <th>Method</th>
                <th>Received By</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data['payments'] as $p)
            <tr>
                <td>{{ $p->paid_on?->format('d M Y') }}</td>
                <td>{{ optional(optional($p->assignment)->student)->full_name ?? '—' }}</td>
                <td>{{ optional(optional(optional($p->assignment)->master)->type)->name ?? '—' }}</td>
                <td>{{ number_format($p->amount, 2) }}</td>
                <td>{{ number_format($p->fine, 2) }}</td>
                <td>{{ number_format($p->discount, 2) }}</td>
                <td>{{ $p->method }}</td>
                <td>{{ optional($p->receiver)->name ?? '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
