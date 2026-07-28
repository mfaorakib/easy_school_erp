@extends('guardianportal::layouts.portal')

@section('title', __('ui.payment_history'))

@section('content')
    <div class="page-head">
        <h1>{{ __('ui.payment_history') }}</h1>
    </div>

    @if ($payments->isEmpty())
        <div class="card">
            <div class="empty">No payments yet.</div>
        </div>
    @else
        <div class="card">
            <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('ui.receipts') }}</th>
                        <th>Student</th>
                        <th>Fee</th>
                        <th>{{ __('ui.amount') }}</th>
                        <th>Method</th>
                        <th>{{ __('ui.date') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $p)
                        <tr>
                            <td>{{ $p->receipt_no }}</td>
                            <td>{{ $p->assignment->student->full_name ?? '—' }}</td>
                            <td>{{ $p->assignment->master->type->name ?? '—' }}</td>
                            <td>{{ number_format($p->amount, 2) }}</td>
                            <td>{{ $p->method }}</td>
                            <td>{{ $p->paid_on->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('portal.payments.receipt', $p) }}" class="btn btn-sm btn-ghost">{{ __('ui.view') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    @endif
@endsection
