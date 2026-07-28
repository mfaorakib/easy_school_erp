@extends('guardianportal::layouts.portal')

@section('title', __('ui.receipt'))

@section('content')
    <style>
        @media print {
            .btn, .portal-tabs, header.portal { display: none; }
        }
    </style>

    <div class="wrap" style="max-width:520px;margin:0 auto;">
        <div class="card">
            <div class="page-head">
                <h1>{{ __('ui.receipt') }}</h1>
                <span class="badge badge-paid">{{ $payment->receipt_no }}</span>
            </div>

            <table>
                <tbody>
                    <tr>
                        <th>{{ __('ui.name') }}</th>
                        <td>{{ $payment->assignment->student->full_name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Fee</th>
                        <td>{{ $payment->assignment->master->type->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('ui.amount') }}</th>
                        <td>{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Fine</th>
                        <td>{{ number_format($payment->fine, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Discount</th>
                        <td>{{ number_format($payment->discount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Total Collected</th>
                        <td><strong>{{ number_format($payment->amount + $payment->fine, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Method</th>
                        <td>{{ $payment->method }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('ui.transaction_reference') }}</th>
                        <td>{{ $payment->reference ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('ui.date') }}</th>
                        <td>{{ $payment->paid_on->format('d M Y') }}</td>
                    </tr>
                </tbody>
            </table>

            <div style="display:flex;gap:.6rem;margin-top:1.2rem;">
                <button class="btn" onclick="window.print()">Print</button>
                <a href="{{ route('portal.payments.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a>
            </div>
        </div>
    </div>
@endsection
