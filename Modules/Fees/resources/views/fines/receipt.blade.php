@extends('layouts.admin')
@section('title', 'Fine Receipt')

@section('content')
<style>@media print{.page-head a,.btn,.sidebar,header,.topbar,.no-print{display:none}}</style>

<div class="page-head">
    <h1>Fine Receipt — {{ $fine->receipt_no }}</h1>
    <a class="btn btn-ghost" href="{{ route('fees.fines.index') }}">{{ __('ui.back') }}</a>
</div>

<div class="card" style="max-width:560px">
    <table>
        <tbody>
        <tr><th>Receipt No</th><td>{{ $fine->receipt_no }}</td></tr>
        <tr><th>{{ __('ui.name') }}</th><td>{{ $fine->studentLabel() }}</td></tr>
        <tr><th>Reason</th><td>{{ $fine->reason }}</td></tr>
        <tr><th>{{ __('ui.amount') }}</th><td>{{ number_format($fine->amount, 2) }}</td></tr>
        <tr><th>Fine Date</th><td>{{ $fine->fine_date->format('d M Y') }}</td></tr>
        <tr><th>Collected On</th><td>{{ $fine->collected_at?->format('d M Y, h:i A') ?? '—' }}</td></tr>
        <tr><th>Imposed By</th><td>{{ optional($fine->imposedBy)->name ?? '—' }}</td></tr>
        </tbody>
    </table>

    <button class="btn" onclick="window.print()">Print</button>
</div>
@endsection
