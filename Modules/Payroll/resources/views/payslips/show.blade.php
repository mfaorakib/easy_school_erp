@extends('layouts.admin')
@section('title', 'Payslip')

@section('content')
<div class="page-head">
    <h1>{{ __('ui.payslip') }} — {{ optional($payslip->staff)->displayName() }}</h1>
    <a href="{{ route('payroll.payslips.index') }}" class="btn btn-sm btn-ghost">{{ __('ui.back') }}</a>
</div>

<div class="card">
    <div class="page-head">
        <h1>{{ optional($payslip->staff)->displayName() }}</h1>
        <div>
            <span class="badge">{{ __('ui.period') }} {{ $payslip->period }}</span>
            @if($payslip->isPaid())
                <span class="badge">{{ __('ui.paid') }}</span>
            @else
                <span class="badge">{{ __('ui.generated') }}</span>
            @endif
        </div>
    </div>

    <div class="grid">
        <div>
            <h3>{{ __('ui.earnings') }}</h3>
            <div class="overflow-x-auto">
            <table>
                <thead><tr><th>{{ __('ui.name') }}</th><th>Amount</th></tr></thead>
                <tbody>
                @foreach($payslip->earnings as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>{{ __('ui.gross') }}</th>
                        <th>{{ number_format($payslip->gross_earnings, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>
        <div>
            <h3>{{ __('ui.deductions') }}</h3>
            <div class="overflow-x-auto">
            <table>
                <thead><tr><th>{{ __('ui.name') }}</th><th>Amount</th></tr></thead>
                <tbody>
                @foreach($payslip->deductions as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>{{ __('ui.deductions') }}</th>
                        <th>{{ number_format($payslip->total_deductions, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>
    </div>

    <div class="page-head">
        <h1>{{ __('ui.net_pay') }}</h1>
        <span class="badge">{{ number_format($payslip->net_pay, 2) }}</span>
    </div>

    @if($payslip->advance_deduction > 0)
        <div class="page-head">
            <h1>{{ __('ui.advance_deduction') }}</h1>
            <span class="badge">{{ number_format($payslip->advance_deduction, 2) }}</span>
        </div>
        <div class="page-head">
            <h1>{{ __('ui.payable_amount') }}</h1>
            <span class="badge">{{ number_format($payslip->payableAmount(), 2) }}</span>
        </div>
    @endif

    @if(!$payslip->isPaid())
        <form method="POST" action="{{ route('payroll.payslips.pay', $payslip) }}">
            @csrf
            <div class="grid">
                <div>
                    <label>{{ __('ui.status') }}</label>
                    <select name="payment_method" id="payment-method" onchange="document.getElementById('bank-account-field').style.display = this.value === 'bank' ? '' : 'none'">
                        <option value="cash">{{ __('ui.cash') }}</option>
                        <option value="bank">Bank</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <div id="bank-account-field" style="display:none">
                    <label>{{ __('ui.bank_name') }}</label>
                    <select name="bank_account_id">
                        <option value="">—</option>
                        @foreach($bankAccounts as $ba)
                            <option value="{{ $ba->id }}">{{ $ba->bank_name }} — {{ $ba->account_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="align-self:end">
                    <button class="btn btn-sm" type="submit">{{ __('ui.mark_paid') }}</button>
                </div>
            </div>
        </form>
    @else
        <p class="badge">Paid on {{ optional($payslip->paid_on)->format('d M Y') }} via {{ $payslip->payment_method }}</p>
    @endif
</div>
@endsection
