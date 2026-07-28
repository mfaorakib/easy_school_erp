@extends('layouts.admin')
@section('title', 'Library Fine Receipt')

@section('content')
<style>@media print{.page-head a,.btn,.sidebar,header,.topbar,.no-print{display:none}}</style>

<div class="page-head">
    <h1>Library Fine Receipt</h1>
    <a class="btn btn-ghost" href="{{ route('library.issue.history') }}">Back</a>
</div>

<div class="card" style="max-width:560px">
    <table>
        <tbody>
        <tr><th>Borrower</th><td>{{ $issue->member->displayLabel() }}</td></tr>
        <tr><th>Book</th><td>{{ optional($issue->book)->title }}</td></tr>
        <tr><th>Issued</th><td>{{ $issue->issue_date->format('d M Y') }}</td></tr>
        <tr><th>Due</th><td>{{ $issue->due_date->format('d M Y') }}</td></tr>
        <tr><th>Returned</th><td>{{ $issue->return_date?->format('d M Y') ?? '—' }}</td></tr>
        <tr><th>Fine Amount</th><td>{{ number_format($issue->fine, 2) }}</td></tr>
        <tr><th>Collected On</th><td>{{ $issue->fine_collected_at?->format('d M Y, h:i A') ?? '—' }}</td></tr>
        </tbody>
    </table>

    <button class="btn" onclick="window.print()">Print</button>
</div>
@endsection
