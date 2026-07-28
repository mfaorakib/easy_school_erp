@extends('staffportal::layouts.portal')

@section('title', __('ui.dashboard'))

@section('content')
    <div class="page-head">
        <h1>{{ __('ui.welcome') }}, {{ $staff->displayName() }}</h1>
    </div>

    @if($hasPendingResignation || $hasPendingAdvance)
        <div class="card">
            <div class="alert alert-success" style="margin:0">
                @if($hasPendingResignation)
                    <div>{{ __('ui.resignation') }}: {{ __('ui.pending') }} &mdash; <a href="{{ route('staffportal.resignation.index') }}">{{ __('ui.view') }}</a></div>
                @endif
                @if($hasPendingAdvance)
                    <div @if($hasPendingResignation) style="margin-top:.4rem" @endif>{{ __('ui.salary_advance') }}: {{ __('ui.pending') }} &mdash; <a href="{{ route('staffportal.advances.index') }}">{{ __('ui.view') }}</a></div>
                @endif
            </div>
        </div>
    @endif

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1.2rem">
        <div class="card">
            <h3 style="margin:0 0 .75rem">{{ __('ui.my_payslips') }}</h3>
            @if($latestPayslip)
                <div style="display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap">
                    <div>
                        <div style="color:var(--muted);font-size:.8rem">{{ __('ui.period') }}: {{ $latestPayslip->period }}</div>
                        <div style="font-size:1.15rem;font-weight:700;margin-top:.15rem">{{ __('ui.amount') }}: {{ number_format((float) $latestPayslip->net_pay, 2) }}</div>
                        <span class="badge {{ $latestPayslip->isPaid() ? 'badge-paid' : 'badge-partial' }}" style="margin-top:.5rem">
                            {{ __('ui.status') }}: {{ $latestPayslip->isPaid() ? __('ui.paid') : __('ui.generated') }}
                        </span>
                    </div>
                </div>
                <div style="margin-top:1rem">
                    <a href="{{ route('staffportal.payslips.index') }}" class="btn btn-ghost">{{ __('ui.view_all') }}</a>
                </div>
            @else
                <div class="empty">{{ __('ui.no_records') }}</div>
            @endif
        </div>

        <div class="card">
            <h3 style="margin:0 0 .75rem">{{ __('ui.my_attendance') }}</h3>
            <div style="color:var(--muted);font-size:.8rem">{{ __('ui.this_month') }}</div>
            <div style="font-size:1.8rem;font-weight:800;margin-top:.15rem">{{ $attendanceCount }}</div>
            <div style="margin-top:1rem">
                <a href="{{ route('staffportal.attendance.index') }}" class="btn btn-ghost">{{ __('ui.view_all') }}</a>
            </div>
        </div>
    </div>
@endsection
