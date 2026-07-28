@extends('layouts.admin')
@section('title', 'Payroll Summary')

@section('content')
<div class="page-head"><h1>{{ __('ui.summary') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('payroll.summary') }}">
        <div class="grid">
            <div>
                <label>{{ __('ui.period') }}</label>
                @if($periods->isNotEmpty())
                    <select name="period" onchange="this.form.submit()">
                        @foreach($periods as $p)
                            <option value="{{ $p }}" {{ $period == $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="month" name="period" value="{{ $period }}">
                @endif
            </div>
            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">{{ __('ui.summary') }}</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="grid">
        <div>
            <label>Payslips</label>
            <div><span class="badge">{{ $data['count'] }}</span></div>
        </div>
        <div>
            <label>{{ __('ui.gross') }}</label>
            <div><strong>{{ number_format($data['gross'], 2) }}</strong></div>
        </div>
        <div>
            <label>{{ __('ui.deductions') }}</label>
            <div><strong>{{ number_format($data['deductions'], 2) }}</strong></div>
        </div>
        <div>
            <label>{{ __('ui.net_pay') }}</label>
            <div><strong>{{ number_format($data['net'], 2) }}</strong></div>
        </div>
        <div>
            <label>{{ __('ui.paid') }}</label>
            <div><strong>{{ number_format($data['paid'], 2) }}</strong></div>
        </div>
        <div>
            <label>Unpaid</label>
            <div><strong>{{ number_format($data['unpaid'], 2) }}</strong></div>
        </div>
    </div>
</div>

<div class="card">
    @if($payslips->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>Staff</th>
                <th>{{ __('ui.gross') }}</th>
                <th>{{ __('ui.deductions') }}</th>
                <th>{{ __('ui.net_pay') }}</th>
                <th>{{ __('ui.status') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($payslips as $p)
            <tr>
                <td>{{ optional($p->staff)->displayName() ?? '—' }}</td>
                <td>{{ number_format($p->gross_earnings, 2) }}</td>
                <td>{{ number_format($p->total_deductions, 2) }}</td>
                <td>{{ number_format($p->net_pay, 2) }}</td>
                <td>
                    @if($p->status === 'paid')
                        <span class="badge">{{ __('ui.paid') }}</span>
                    @else
                        <span class="badge">{{ __('ui.generated') }}</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
