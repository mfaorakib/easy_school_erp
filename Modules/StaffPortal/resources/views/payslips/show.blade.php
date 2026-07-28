@extends('staffportal::layouts.portal')

@section('title', __('ui.payslip') . ' — ' . $payslip->period)

@section('content')
    <div class="page-head">
        <h1>{{ __('ui.payslip') }} &mdash; {{ $payslip->period }}</h1>
        <a href="{{ route('staffportal.payslips.index') }}" class="btn btn-ghost">&larr; {{ __('ui.back') }}</a>
    </div>

    <div class="card">
        <div class="page-head">
            <div>
                <div style="color:var(--muted);font-size:.85rem">{{ __('ui.period') }}</div>
                <strong style="font-size:1.1rem">{{ $payslip->period }}</strong>
            </div>
            <div>
                @if($payslip->isPaid())
                    <span class="badge badge-paid">{{ __('ui.paid') }}</span>
                @else
                    <span class="badge">{{ __('ui.generated') }}</span>
                @endif
            </div>
        </div>

        <table>
            <tbody>
                <tr>
                    <td>{{ __('ui.basic_salary') }}</td>
                    <td style="text-align:end">{{ number_format((float) $payslip->basic_salary, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3 style="margin-top:0">{{ __('ui.earnings') }}</h3>
        @if($payslip->earnings->isEmpty())
            <div class="empty">{{ __('ui.no_records') }}</div>
        @else
            <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('ui.component') }}</th>
                        <th style="text-align:end">{{ __('ui.earnings') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payslip->earnings as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td style="text-align:end">{{ number_format((float) $item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>{{ __('ui.gross') }} {{ __('ui.earnings') }}</th>
                        <th style="text-align:end">{{ number_format((float) $payslip->gross_earnings, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
            </div>
        @endif
    </div>

    <div class="card">
        <h3 style="margin-top:0">{{ __('ui.deductions') }}</h3>
        @if($payslip->deductions->isEmpty())
            <div class="empty">{{ __('ui.no_records') }}</div>
        @else
            <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('ui.component') }}</th>
                        <th style="text-align:end">{{ __('ui.deductions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payslip->deductions as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td style="text-align:end">{{ number_format((float) $item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>{{ __('ui.deductions') }}</th>
                        <th style="text-align:end">{{ number_format((float) $payslip->total_deductions, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="page-head" style="margin-bottom:0">
            <h1>{{ __('ui.net_pay') }}</h1>
            <span class="badge" style="font-size:1rem">{{ number_format((float) $payslip->net_pay, 2) }}</span>
        </div>

        @if((float) $payslip->advance_deduction > 0)
            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border)">
                <div style="color:var(--muted);font-size:.9rem">
                    {{ __('ui.advance_deduction') }}: -{{ number_format((float) $payslip->advance_deduction, 2) }}
                </div>
                <div style="margin-top:.4rem;font-size:1.2rem;font-weight:800;color:var(--primary)">
                    {{ __('ui.payable_amount') }}: {{ number_format($payslip->payableAmount(), 2) }}
                </div>
            </div>
        @endif

        @if($payslip->isPaid())
            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border);color:var(--muted);font-size:.9rem">
                {{ __('ui.payment_method') }}: {{ $payslip->payment_method ?? '—' }}
                &middot; {{ __('ui.date') }}: {{ optional($payslip->paid_on)->format('d M Y') ?? '—' }}
                @if($payslip->bankAccount)
                    &middot; {{ __('ui.bank_name') }}: {{ $payslip->bankAccount->label() }}
                @endif
            </div>
        @endif
    </div>
@endsection
