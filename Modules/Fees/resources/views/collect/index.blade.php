@extends('layouts.admin')
@section('title', __('ui.fee_collect'))

@section('content')
@php($discounts = \Modules\Fees\Models\FeeDiscount::where('is_active', true)->orderBy('name')->get())
<div class="page-head"><h1>{{ __('ui.fee_collect') }}</h1></div>

{{-- Search --}}
<div class="card">
    <form method="GET" action="{{ route('fees.collect.index') }}">
        <div class="grid">
            <div style="grid-column:1/-1"><label>{{ __('ui.search') }} student (name or admission no)</label>
                <input name="q" value="{{ $q }}" placeholder="e.g. Nadia or 1">
            </div>
            <div style="display:flex;align-items:flex-end"><button class="btn">{{ __('ui.search') }}</button></div>
        </div>
    </form>
    @if($results->isNotEmpty())
        <div class="overflow-x-auto">
        <table style="margin-top:1rem">
            <thead><tr><th>Adm #</th><th>{{ __('ui.name') }}</th><th></th></tr></thead>
            <tbody>
            @foreach($results as $r)
                <tr>
                    <td>{{ $r->admission_no }}</td><td>{{ $r->full_name }}</td>
                    <td><a class="btn btn-sm" href="{{ route('fees.collect.index', ['student_id' => $r->id]) }}">Open</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>

@if($student)
<div class="card">
    <h3 style="margin-top:0">{{ $student->full_name }} <span class="badge">Adm #{{ $student->admission_no }}</span></h3>

    @if(empty($ledger))
        <div class="empty">No fees assigned to this student.</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>Fee</th><th>{{ __('ui.amount') }}</th><th>Discount</th><th>{{ __('ui.paid') }}</th>
            <th>{{ __('ui.due') }}</th><th>Fine</th><th>{{ __('ui.status') }}</th><th>{{ __('ui.collect') }}</th>
        </tr></thead>
        <tbody>
        @foreach($ledger as $row)
            @php($b = $row['balance'])
            <tr>
                <td><strong>{{ optional($row['assignment']->master->type)->name }}</strong><br>
                    <span class="badge">{{ optional($row['assignment']->master->group)->name }}</span></td>
                <td>{{ number_format($b['amount'], 2) }}</td>
                <td>
                    {{ number_format($b['discount'], 2) }}
                    @foreach($row['assignment']->discounts as $d)
                        <div class="badge" style="display:block;margin-top:.25rem;font-size:.7rem">
                            {{ $d->name }}
                            <form method="POST" action="{{ route('fees.assignments.discounts.detach', [$row['assignment'], $d]) }}" style="display:inline" onsubmit="return confirm('{{ __('ui.detach') }}?')">
                                @csrf @method('DELETE')<button type="submit" style="background:none;border:0;color:#dc2626;cursor:pointer;font-size:.7rem" title="{{ __('ui.detach') }}"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                            </form>
                        </div>
                    @endforeach
                    <details style="margin-top:.35rem">
                        <summary style="cursor:pointer;font-size:.72rem;color:#4f46e5">+ {{ __('ui.discount') }}</summary>
                        <form method="POST" action="{{ route('fees.assignments.discounts.attach', $row['assignment']) }}" style="margin-top:.4rem;display:flex;flex-direction:column;gap:.3rem;min-width:180px">
                            @csrf
                            <select name="fee_discount_id" required style="font-size:.75rem">
                                <option value="">— {{ __('ui.discount') }} —</option>
                                @foreach($discounts as $disc)
                                    <option value="{{ $disc->id }}">{{ $disc->name }} ({{ $disc->amount_type === 'percentage' ? $disc->amount.'%' : number_format($disc->amount,0) }})</option>
                                @endforeach
                            </select>
                            <input type="text" name="reason" placeholder="{{ __('ui.reason') }}" required style="font-size:.75rem;padding:.3rem .4rem">
                            <button class="btn btn-sm" type="submit">{{ __('ui.attach') }}</button>
                        </form>
                    </details>
                </td>
                <td>{{ number_format($b['paid'], 2) }}</td>
                <td><strong>{{ number_format($b['due'], 2) }}</strong></td>
                <td>{{ number_format($b['fineDue'], 2) }}</td>
                <td><span class="badge">{{ ucfirst($b['status']) }}</span></td>
                <td>
                    @if($b['due'] > 0)
                    <form method="POST" action="{{ route('fees.collect.pay') }}" style="display:flex;gap:.35rem;flex-wrap:wrap;align-items:center">
                        @csrf
                        <input type="hidden" name="fee_assignment_id" value="{{ $row['assignment']->id }}">
                        <input type="hidden" name="paid_on" value="{{ now()->toDateString() }}">
                        <input name="amount" type="number" step="any" value="{{ $b['due'] }}" style="width:90px" title="Amount">
                        <input name="fine" type="number" step="any" value="{{ $b['fineDue'] }}" style="width:70px" title="Fine">
                        <select name="method" style="width:110px">
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm->name }}">{{ $pm->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm">{{ __('ui.collect') }}</button>
                    </form>
                    @else
                        <span class="badge"><i class="fa-solid fa-check" aria-hidden="true"></i> Paid</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif

    @if($carry->isNotEmpty())
        <h3>Carried-forward dues</h3>
        <div class="overflow-x-auto">
        <table>
            <thead><tr><th>From year</th><th>{{ __('ui.amount') }}</th><th>{{ __('ui.paid') }}</th><th>Note</th></tr></thead>
            <tbody>
            @foreach($carry as $c)
                <tr><td>#{{ $c->from_year_id ?? '—' }}</td><td>{{ number_format($c->amount, 2) }}</td>
                    <td>{{ number_format($c->paid, 2) }}</td><td>{{ $c->note }}</td></tr>
            @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>
@endif
@endsection
