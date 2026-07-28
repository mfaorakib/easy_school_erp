@extends('layouts.admin')
@section('title', 'Payslips')

@section('content')
<div class="page-head">
    <h1>{{ __('ui.payslips') }}</h1>
    <a href="{{ route('payroll.payslips.bankAdvice', ['period' => $period ?? '']) }}" class="btn btn-sm btn-ghost">{{ __('ui.bank_advice') }}</a>
</div>

<div class="card">
    <form method="GET" action="{{ route('payroll.payslips.index') }}">
        <div class="grid">
            <div>
                <label>{{ __('ui.period') }}</label>
                <select name="period">
                    <option value="">—</option>
                    @foreach($periods as $p)
                        <option value="{{ $p }}" {{ $period === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>{{ __('ui.status') }}</label>
                <select name="status">
                    <option value="">—</option>
                    <option value="generated" {{ $status === 'generated' ? 'selected' : '' }}>{{ __('ui.generated') }}</option>
                    <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>{{ __('ui.paid') }}</option>
                </select>
            </div>
            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">Filter</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    @if($payslips->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <form method="POST" action="{{ route('payroll.payslips.bulkPay') }}" onsubmit="return confirm('Mark selected payslips paid?')">
        @csrf
        <input type="hidden" name="period" value="{{ $period }}">
        <div class="grid" style="margin-bottom:1rem">
            <div>
                <label>{{ __('ui.payment_method') }}</label>
                <select name="payment_method" id="bulk-payment-method" onchange="document.getElementById('bulk-bank-account-field').style.display = this.value === 'bank' ? '' : 'none'">
                    <option value="cash">{{ __('ui.cash') }}</option>
                    <option value="bank">Bank</option>
                </select>
            </div>
            <div id="bulk-bank-account-field" style="display:none">
                <label>{{ __('ui.bank_name') }}</label>
                <select name="bank_account_id">
                    <option value="">—</option>
                    @foreach($bankAccounts as $ba)
                        <option value="{{ $ba->id }}">{{ $ba->bank_name }} — {{ $ba->account_name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">{{ __('ui.bulk_pay') }}</button>
            </div>
        </div>
        <div class="overflow-x-auto">
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th>{{ __('ui.period') }}</th>
                    <th>Staff</th>
                    <th>{{ __('ui.basic_salary') }}</th>
                    <th>{{ __('ui.deductions') }}</th>
                    <th>{{ __('ui.net_pay') }}</th>
                    <th>{{ __('ui.status') }}</th>
                    <th>{{ __('ui.actions') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($payslips as $p)
                <tr>
                    <td>
                        @if($p->status === 'generated')
                            <input type="checkbox" name="payslip_ids[]" value="{{ $p->id }}">
                        @endif
                    </td>
                    <td><strong>{{ $p->period }}</strong></td>
                    <td>{{ optional($p->staff)->displayName() ?? '—' }}</td>
                    <td>{{ number_format($p->basic_salary, 2) }}</td>
                    <td>{{ number_format($p->total_deductions, 2) }}</td>
                    <td>{{ number_format($p->net_pay, 2) }}</td>
                    <td>
                        @if($p->status === 'paid')
                            <span class="badge">{{ __('ui.paid') }}</span>
                        @else
                            <span class="badge">{{ __('ui.generated') }}</span>
                        @endif
                    </td>
                    <td class="actions">
                        <a href="{{ route('payroll.payslips.show', $p) }}" class="btn btn-sm btn-ghost">View</a>
                        <button type="button" class="btn btn-sm btn-danger" onclick="deletePayslip({{ $p->id }})">{{ __('ui.delete') }}</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    </form>

    <form method="POST" action="{{ route('payroll.payslips.index') }}" id="delete-payslip-form" style="display:none">
        @csrf @method('DELETE')
    </form>
    <script>
        function deletePayslip(id) {
            if (!confirm('Delete?')) return;
            var form = document.getElementById('delete-payslip-form');
            form.action = '{{ url('payroll/payslips') }}/' + id;
            form.submit();
        }
    </script>
    @endif
</div>
@endsection
