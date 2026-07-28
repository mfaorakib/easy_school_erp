@extends('layouts.admin')
@section('title', __('ui.receipt'))

@section('content')
<style>@media print{.page-head a,.btn,.sidebar,header,.topbar,.no-print{display:none}}</style>

<div class="page-head">
    <h1>{{ __('ui.receipt') }} — {{ $payment->receipt_no }}</h1>
    <a class="btn btn-ghost" href="{{ route('fees.collect.index', ['student_id' => $payment->assignment->student_id]) }}">{{ __('ui.back') }}</a>
</div>

<div class="card" style="max-width:560px">
    <table>
        <tbody>
        <tr><th>Receipt No</th><td>{{ $payment->receipt_no }}</td></tr>
        <tr><th>{{ __('ui.name') }}</th><td>{{ $payment->assignment->student->full_name ?? '—' }}</td></tr>
        <tr><th>Fee</th><td>{{ $payment->assignment->master->type->name ?? '—' }}</td></tr>
        <tr><th>{{ __('ui.amount') }}</th><td>{{ number_format($payment->amount, 2) }}</td></tr>
        <tr><th>Fine</th><td>{{ number_format($payment->fine, 2) }}</td></tr>
        <tr><th>Discount</th><td>{{ number_format($payment->discount, 2) }}</td></tr>
        <tr><th>Total</th><td>{{ number_format($payment->amount + $payment->fine, 2) }}</td></tr>
        <tr><th>Method</th><td>{{ $payment->method }}</td></tr>
        <tr><th>{{ __('ui.transaction_reference') }}</th><td>{{ $payment->reference ?? '—' }}</td></tr>
        <tr><th>Received By</th><td>{{ $payment->receiver->name ?? 'System' }}</td></tr>
        <tr><th>{{ __('ui.date') }}</th><td>{{ $payment->paid_on->format('d M Y') }}</td></tr>
        </tbody>
    </table>

    <button class="btn" onclick="window.print()">Print</button>
</div>

@if($payment->refunded_amount > 0 || $payment->refundableAmount() > 0)
<div class="no-print card" style="max-width:560px;margin-top:1rem">
    <h3 style="margin-top:0">Refund</h3>

    @if($payment->refunded_amount > 0)
        <p>Refunded so far: <strong>{{ number_format($payment->refunded_amount, 2) }}</strong></p>
    @endif

    @if($payment->isFullyRefunded())
        <p><span class="badge">Fully refunded</span></p>
    @elseif($payment->refundableAmount() > 0)
        <form method="POST" action="{{ route('fees.payments.refund', $payment) }}" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end">
            @csrf
            <div>
                <label>{{ __('ui.amount') }}</label><br>
                <input type="number" name="amount" step="0.01" min="0.01" max="{{ $payment->refundableAmount() }}" placeholder="Max: {{ number_format($payment->refundableAmount(), 2) }}" required style="width:140px">
            </div>
            <div>
                <label>{{ __('ui.reason') }}</label><br>
                <input type="text" name="reason" required style="width:220px">
            </div>
            <button class="btn btn-sm" type="submit">Refund</button>
        </form>
    @endif
</div>
@endif
@endsection
