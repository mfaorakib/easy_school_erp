@extends('layouts.admin')
@section('title', __('ui.salary_advances'))

@section('content')
<div class="page-head"><h1>{{ __('ui.salary_advances') }}</h1></div>

<div class="card">
    <h3 style="margin-top:0">{{ __('ui.approvals') }}</h3>
    @if($pending->isEmpty())
        <div class="empty">{{ __('ui.no_advances') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>{{ __('ui.staff') }}</th>
                <th>{{ __('ui.amount') }}</th>
                <th>{{ __('ui.reason') }}</th>
                <th>{{ __('ui.actions') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($pending as $a)
            <tr>
                <td><strong>{{ optional($a->staff)->displayName() }}</strong></td>
                <td>{{ number_format($a->amount, 2) }}</td>
                <td>{{ \Illuminate\Support\Str::limit($a->reason, 60) }}</td>
                <td class="actions">
                    <form method="POST" action="{{ route('payroll.advances.review', $a) }}" style="display:inline">
                        @csrf
                        <input type="hidden" name="status" value="approved">
                        <button class="btn btn-sm" type="submit" onclick="return confirm('Approve this advance? It will post immediately as an expense and be recovered from a future payslip.')">{{ __('ui.approve') }}</button>
                    </form>
                    <form method="POST" action="{{ route('payroll.advances.review', $a) }}" style="display:inline">
                        @csrf
                        <input type="hidden" name="status" value="rejected">
                        <input type="text" name="review_note" maxlength="255" placeholder="note">
                        <button class="btn btn-sm btn-danger" type="submit">{{ __('ui.reject') }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>

<div class="card">
    <h3 style="margin-top:0">{{ __('ui.history') }}</h3>
    @if($history->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.staff') }}</th><th>{{ __('ui.amount') }}</th><th>{{ __('ui.recovered') }}</th><th>{{ __('ui.status') }}</th></tr></thead>
        <tbody>
        @foreach($history as $a)
            <tr>
                <td>{{ optional($a->staff)->displayName() }}</td>
                <td>{{ number_format($a->amount, 2) }}</td>
                <td>{{ number_format($a->recovered_amount, 2) }}</td>
                <td><span class="badge">{{ ucfirst($a->status) }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
