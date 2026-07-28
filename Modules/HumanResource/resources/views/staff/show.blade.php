@extends('layouts.admin')
@section('title', $staff->displayName().' — '.__('ui.staff'))

@section('content')
<div class="page-head">
    <h1>
        {{ $staff->displayName() }}
        <span class="badge">{{ $staff->is_active ? __('ui.active') : __('ui.inactive') }}</span>
    </h1>
    <div style="display:flex;gap:.6rem;align-items:center;flex-wrap:wrap">
        @php($apptTemplateId = \Modules\Documents\Models\CertificateTemplate::where('name', 'Appointment Letter')->value('id'))
        @if($apptTemplateId)
            <form method="POST" action="{{ route('documents.certificates.generate') }}" target="_blank" style="display:inline">
                @csrf
                <input type="hidden" name="template_id" value="{{ $apptTemplateId }}">
                <input type="hidden" name="holder_ids[]" value="{{ $staff->id }}">
                <button class="btn btn-sm" type="submit">Print Appointment Letter</button>
            </form>
        @endif
        <a href="{{ route('hr.staff.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a>
    </div>
</div>

{{-- Profile card --}}
<div class="card">
    <div style="display:flex;gap:1.25rem;flex-wrap:wrap;align-items:flex-start">
        @php($photoUrl = \Modules\Builder\Services\BuilderService::media($staff->photo))
        @if($photoUrl)
            <img src="{{ $photoUrl }}" alt="{{ $staff->displayName() }}"
                 style="width:88px;height:88px;border-radius:50%;object-fit:cover;flex-shrink:0">
        @else
            <div style="width:88px;height:88px;border-radius:50%;flex-shrink:0;
                        background:linear-gradient(135deg,var(--primary),#818cf8);
                        display:flex;align-items:center;justify-content:center;
                        color:#fff;font-weight:800;font-size:2rem">
                {{ strtoupper(substr($staff->displayName(), 0, 1)) }}
            </div>
        @endif

        <div style="flex:1;min-width:260px">
            <div style="color:var(--muted);font-size:.85rem;margin-bottom:.5rem">
                {{ __('ui.staff') }} No: <strong style="color:var(--text)">{{ $staff->staff_no ?? '—' }}</strong>
                @if($staff->designation) &middot; {{ $staff->designation->title }} @endif
                @if($staff->department) &middot; {{ $staff->department->name }} @endif
            </div>

            <div class="grid">
                <div><label>Date of Joining</label><p style="margin:0">{{ optional($staff->date_of_joining)->format('d M Y') ?? '—' }}</p></div>
                <div>
                    <label>Employment Status</label>
                    <p style="margin:0">
                        @if($staff->leaving_date)
                            <span style="color:var(--warning)">Left: {{ $staff->leaving_date->format('d M Y') }}</span>
                        @else
                            <span style="color:var(--success)">Currently employed</span>
                        @endif
                    </p>
                </div>
                <div><label>{{ __('ui.mobile') }}</label><p style="margin:0">{{ $staff->mobile ?? '—' }}</p></div>
                <div><label>{{ __('ui.email') }}</label><p style="margin:0">{{ $staff->email ?? '—' }}</p></div>
                <div><label>Date of Birth</label><p style="margin:0">{{ optional($staff->date_of_birth)->format('d M Y') ?? '—' }}</p></div>
                @if($staff->qualification)
                    <div><label>Qualification</label><p style="margin:0">{{ $staff->qualification }}</p></div>
                @endif
                @if($staff->current_address)
                    <div><label>Address</label><p style="margin:0">{{ $staff->current_address }}</p></div>
                @endif
            </div>

            @if($staff->bank_name || $staff->bank_account_no || $staff->bank_branch)
                <div class="grid" style="margin-top:.25rem">
                    @if($staff->bank_name)
                        <div><label>{{ __('ui.bank_name') }}</label><p style="margin:0">{{ $staff->bank_name }}</p></div>
                    @endif
                    @if($staff->bank_account_no)
                        <div><label>{{ __('ui.bank_account_no') }}</label><p style="margin:0">{{ $staff->bank_account_no }}</p></div>
                    @endif
                    @if($staff->bank_branch)
                        <div><label>{{ __('ui.bank_branch') }}</label><p style="margin:0">{{ $staff->bank_branch }}</p></div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Academic Year picker — scopes attendance/payslips/leave below ONLY on this page --}}
<div class="card">
    <div style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap">
        <div>
            <label style="margin:0 0 .3rem">Academic Year</label>
            <select onchange="location.href='{{ route('hr.staff.show', $staff) }}?year='+this.value" style="width:auto">
                @foreach($academicYears as $ay)
                    <option value="{{ $ay->id }}" {{ optional($year)->id === $ay->id ? 'selected' : '' }}>
                        {{ $ay->title }}{{ $ay->is_active ? ' — '.__('ui.active') : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <small style="color:var(--muted)">
            Changes only what this profile page shows below — it does not affect the system's active session for anyone else.
        </small>
    </div>
</div>

{{-- Attendance --}}
<div class="card">
    <h3 style="margin-top:0">{{ __('ui.staff_attendance') }}</h3>

    @if($attendanceSummary->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
        <div class="stat-grid" style="margin-bottom:1.25rem">
            @foreach($attendanceSummary as $code => $count)
                @php($label = \Modules\Attendance\Enums\AttendanceStatus::tryFrom($code)?->label() ?? $code)
                <div class="stat"><div class="n">{{ $count }}</div><div class="l">{{ $label }}</div></div>
            @endforeach
        </div>
    @endif

    @if($attendance->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
        <div class="overflow-x-auto" style="max-height:340px;overflow-y:auto">
            <table>
                <thead><tr><th>{{ __('ui.date') }}</th><th>{{ __('ui.status') }}</th><th>Note</th></tr></thead>
                <tbody>
                @foreach($attendance as $a)
                    <tr>
                        <td>{{ optional($a->date)->format('d M Y') }}</td>
                        <td><span class="badge">{{ $a->status?->label() ?? '—' }}</span></td>
                        <td>{{ $a->note ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Payslips --}}
<div class="card">
    <h3 style="margin-top:0">{{ __('ui.payslips') }}</h3>

    @if($payslips->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
        <div class="overflow-x-auto">
        <table>
            <thead>
                <tr>
                    <th>{{ __('ui.period') }}</th>
                    <th>{{ __('ui.gross') }}</th>
                    <th>{{ __('ui.deductions') }}</th>
                    <th>{{ __('ui.net_pay') }}</th>
                    <th>{{ __('ui.status') }}</th>
                    <th>{{ __('ui.payment_method') }}</th>
                    <th>{{ __('ui.actions') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($payslips as $p)
                <tr>
                    <td><strong>{{ $p->period }}</strong></td>
                    <td>{{ number_format($p->gross_earnings, 2) }}</td>
                    <td>{{ number_format($p->total_deductions, 2) }}</td>
                    <td>
                        {{ number_format($p->net_pay, 2) }}
                        @if($p->advance_deduction > 0)
                            <div style="font-size:.75rem;color:var(--muted);margin-top:.2rem">
                                {{ __('ui.advance_deduction') }}: -{{ number_format($p->advance_deduction, 2) }}<br>
                                {{ __('ui.payable_amount') }}: <strong style="color:var(--text)">{{ number_format($p->payableAmount(), 2) }}</strong>
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($p->status === 'paid')
                            <span class="badge">{{ __('ui.paid') }}</span>
                        @else
                            <span class="badge">{{ __('ui.generated') }}</span>
                        @endif
                    </td>
                    <td>
                        @if($p->status === 'paid')
                            {{ $p->payment_method === 'cash' ? __('ui.cash') : 'Bank' }}
                            @if($p->bankAccount)
                                <div style="font-size:.75rem;color:var(--muted)">{{ $p->bankAccount->label() }}</div>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td class="actions">
                        <a href="{{ route('payroll.payslips.show', $p) }}" class="btn btn-sm btn-ghost">{{ __('ui.view') }}</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>

{{-- Leave applications --}}
<div class="card">
    <h3 style="margin-top:0">{{ __('ui.leave_applications') }}</h3>

    @if($leaveApplications->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
        <div class="overflow-x-auto">
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>{{ __('ui.from_to') }}</th>
                    <th>{{ __('ui.days') }}</th>
                    <th>{{ __('ui.status') }}</th>
                    <th>{{ __('ui.reason') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($leaveApplications as $l)
                <tr>
                    <td>{{ optional($l->type)->name ?? '—' }}</td>
                    <td>{{ optional($l->from_date)->format('d M Y') }} — {{ optional($l->to_date)->format('d M Y') }}</td>
                    <td>{{ $l->days }}</td>
                    <td>
                        @if($l->status === 'pending')
                            <span class="badge">{{ __('ui.pending') }}</span>
                        @elseif($l->status === 'approved')
                            <span class="badge">Approved</span>
                        @else
                            <span class="badge">{{ __('ui.rejected') }}</span>
                        @endif
                        @if($l->status === 'rejected' && $l->review_note)
                            <div style="font-size:.75rem;color:var(--muted);margin-top:.2rem">{{ $l->review_note }}</div>
                        @endif
                    </td>
                    <td>{{ $l->reason ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>
@endsection
