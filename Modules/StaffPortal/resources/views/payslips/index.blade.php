@extends('staffportal::layouts.portal')

@section('title', __('ui.my_payslips'))

@section('content')
    <div class="page-head">
        <h1>{{ __('ui.my_payslips') }}</h1>
    </div>

    @php($__hasAdvances = $payslips->contains(fn ($p) => (float) $p->advance_deduction > 0))

    <div class="card">
        @if($payslips->isEmpty())
            <div class="empty">{{ __('ui.no_records') }}</div>
        @else
            <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('ui.period') }}</th>
                        <th>{{ __('ui.gross') }} {{ __('ui.earnings') }}</th>
                        <th>{{ __('ui.deductions') }}</th>
                        @if($__hasAdvances)
                            <th>{{ __('ui.advance_deduction') }}</th>
                        @endif
                        <th>{{ __('ui.net_pay') }}</th>
                        <th>{{ __('ui.status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payslips as $payslip)
                        <tr>
                            <td><strong>{{ $payslip->period }}</strong></td>
                            <td>{{ number_format((float) $payslip->gross_earnings, 2) }}</td>
                            <td>{{ number_format((float) $payslip->total_deductions, 2) }}</td>
                            @if($__hasAdvances)
                                <td>
                                    @if((float) $payslip->advance_deduction > 0)
                                        {{ number_format((float) $payslip->advance_deduction, 2) }}
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                            @endif
                            <td>
                                {{ number_format((float) $payslip->net_pay, 2) }}
                                @if((float) $payslip->advance_deduction > 0)
                                    <div style="color:var(--muted);font-size:.8rem">
                                        {{ __('ui.payable_amount') }}: {{ number_format($payslip->payableAmount(), 2) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($payslip->isPaid())
                                    <span class="badge badge-paid">{{ __('ui.paid') }}</span>
                                @else
                                    <span class="badge">{{ __('ui.generated') }}</span>
                                @endif
                            </td>
                            <td class="actions">
                                <a href="{{ route('staffportal.payslips.show', $payslip) }}" class="btn btn-ghost">{{ __('ui.view') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
@endsection
