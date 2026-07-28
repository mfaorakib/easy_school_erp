@extends('layouts.admin')
@section('title', $student->full_name)

@section('content')
<style>
    .profile-avatar{width:84px;height:84px;border-radius:50%;object-fit:cover;flex-shrink:0;
        background:linear-gradient(135deg,var(--primary),#818cf8);color:#fff;font-weight:800;font-size:1.9rem;
        display:flex;align-items:center;justify-content:center}
    .field-label{color:var(--muted);font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem}
    .field-value{font-size:.92rem}
    .attendance-scroll{max-height:340px;overflow-y:auto;margin-top:1rem}
</style>

@php
    $photoUrl = \Modules\Documents\Services\DocumentService::media($student->photo);
@endphp

<div class="page-head">
    <h1>{{ $student->full_name }}</h1>
    <a href="{{ route('academic.students.index') }}" class="btn btn-ghost">&larr; {{ __('ui.back') }}</a>
</div>

{{-- Header card --}}
<div class="card">
    <div style="display:flex;gap:1.5rem;flex-wrap:wrap;align-items:flex-start">
        @if($photoUrl)
            <img src="{{ $photoUrl }}" alt="{{ $student->full_name }}" class="profile-avatar">
        @else
            <div class="profile-avatar">{{ strtoupper(substr($student->full_name, 0, 1)) }}</div>
        @endif

        <div style="flex:1;min-width:260px">
            <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap">
                <h2 style="margin:0;font-size:1.2rem">{{ $student->full_name }}</h2>
                <span class="badge">Adm # {{ $student->admission_no }}</span>
                @if($student->unique_id)
                    <span class="badge">{{ __('ui.unique_id') }}: {{ $student->unique_id }}</span>
                @endif
                <span class="badge" style="color:{{ $student->is_active ? 'var(--success)' : 'var(--danger)' }}">
                    {{ $student->is_active ? __('ui.active') : __('ui.inactive') }}
                </span>
            </div>

            <div class="grid" style="margin-top:1rem">
                <div>
                    <div class="field-label">Admission Date</div>
                    <div class="field-value">{{ optional($student->admission_date)->format('d M Y') ?? '—' }}</div>
                </div>
                <div>
                    <div class="field-label">{{ __('ui.class') }}</div>
                    <div class="field-value">{{ optional(optional($record)->schoolClass)->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="field-label">{{ __('ui.section') }}</div>
                    <div class="field-value">{{ optional(optional($record)->section)->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="field-label">Roll</div>
                    <div class="field-value">{{ optional($record)->roll_no ?? '—' }}</div>
                </div>
                <div>
                    <div class="field-label">{{ __('ui.guardian') }}</div>
                    <div class="field-value">{{ optional($student->guardian)->guardian_name ?? '—' }}</div>
                </div>
                <div>
                    <div class="field-label">{{ __('ui.guardian') }} {{ __('ui.mobile') }}</div>
                    <div class="field-value">{{ optional($student->guardian)->guardian_mobile ?? '—' }}</div>
                </div>
                <div>
                    <div class="field-label">{{ __('ui.mobile') }}</div>
                    <div class="field-value">{{ $student->mobile ?? '—' }}</div>
                </div>
                <div>
                    <div class="field-label">{{ __('ui.email') }}</div>
                    <div class="field-value">{{ $student->email ?? '—' }}</div>
                </div>
                <div>
                    <div class="field-label">Date of Birth</div>
                    <div class="field-value">{{ optional($student->date_of_birth)->format('d M Y') ?? '—' }}</div>
                </div>
                <div>
                    <div class="field-label">National ID</div>
                    <div class="field-value">{{ $student->national_id_no ?? '—' }}</div>
                </div>
                <div style="grid-column:1/-1">
                    <div class="field-label">Address</div>
                    <div class="field-value">{{ $student->current_address ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Academic Year picker — scoped to THIS page's view only, never the system-wide active year --}}
<div class="card">
    <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap">
        <label style="margin:0;flex-shrink:0">Academic Year</label>
        <select onchange="location.href='{{ route('academic.students.show', $student->id) }}?year='+this.value" style="width:auto">
            @forelse($academicYears as $ay)
                <option value="{{ $ay->id }}" {{ optional($year)->id == $ay->id ? 'selected' : '' }}>
                    {{ $ay->title }}{{ $ay->is_active ? ' — ' . __('ui.active') : '' }}
                </option>
            @empty
                <option value="">—</option>
            @endforelse
        </select>
    </div>
    <p style="color:var(--muted);font-size:.8rem;margin:.6rem 0 0">
        This only switches what this profile page displays below — it does not change the school's system-wide active academic year.
    </p>
</div>

@if(!$year)
    <div class="card"><div class="empty">No academic year is available to show records for.</div></div>
@else

{{-- Attendance --}}
<div class="card">
    <h3 style="margin-top:0">Attendance — {{ $year->title }}</h3>
    <div class="stat-grid">
        @foreach(\Modules\Attendance\Enums\AttendanceStatus::cases() as $case)
            <div class="stat">
                <div class="n">{{ $attendanceSummary[$case->value] ?? 0 }}</div>
                <div class="l">{{ $case->label() }}</div>
            </div>
        @endforeach
    </div>

    @if($attendance->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="attendance-scroll overflow-x-auto">
        <table>
            <thead><tr><th>{{ __('ui.date') }}</th><th>{{ __('ui.status') }}</th><th>Note</th></tr></thead>
            <tbody>
            @foreach($attendance->sortByDesc('date') as $a)
                <tr>
                    <td>{{ $a->date->format('d M Y') }}</td>
                    <td><span class="badge">{{ $a->status instanceof \BackedEnum ? $a->status->label() : $a->status }}</span></td>
                    <td>{{ $a->note ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- Fee ledger --}}
<div class="card">
    <h3 style="margin-top:0">Fee Ledger — {{ $year->title }}</h3>
    @if($feeAssignments->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>Fee</th><th>{{ __('ui.amount') }}</th><th>{{ __('ui.paid') }}</th><th>{{ __('ui.due') }}</th><th>{{ __('ui.status') }}</th>
        </tr></thead>
        <tbody>
        @foreach($feeAssignments as $row)
            @php
                $b = $row['balance'];
                $statusColor = match($b['status']) {
                    'paid' => 'var(--success)',
                    'partial' => 'var(--primary)',
                    default => 'var(--danger)',
                };
            @endphp
            <tr>
                <td><strong>{{ optional(optional($row['assignment']->master)->type)->name ?? '—' }}</strong></td>
                <td>{{ number_format($b['net'], 2) }}</td>
                <td>{{ number_format($b['settled'], 2) }}</td>
                <td>{{ number_format($b['due'], 2) }}</td>
                <td><span class="badge" style="color:{{ $statusColor }}">{{ ucfirst($b['status']) }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>

{{-- Exam results --}}
<div class="card">
    <h3 style="margin-top:0">Exam Results — {{ $year->title }}</h3>
    @if($examResults->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>{{ __('ui.exam') }}</th><th>Marks</th><th>{{ __('ui.gpa') }}</th><th>{{ __('ui.grade') }}</th>
            <th>{{ __('ui.result') }}</th><th>{{ __('ui.position') }}</th>
        </tr></thead>
        <tbody>
        @foreach($examResults as $r)
            <tr>
                <td><strong>{{ optional($r->exam)->name ?? '—' }}</strong></td>
                <td>{{ $r->total_marks }} / {{ $r->total_full_mark }}</td>
                <td>{{ $r->gpa }}</td>
                <td><span class="badge">{{ $r->grade ?: '—' }}</span></td>
                <td><span class="badge" style="color:{{ $r->is_pass ? 'var(--success)' : 'var(--danger)' }}">{{ $r->is_pass ? __('ui.pass') : __('ui.fail') }}</span></td>
                <td>{{ $r->position ?? '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>

@endif

{{-- Documents — not year-scoped, always shows all of them --}}
<div class="card">
    <h3 style="margin-top:0">{{ __('ui.student_documents') }}</h3>
    @if($documents->isEmpty())
        <div class="empty">{{ __('ui.no_documents_yet') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.student_documents') }}</th><th>{{ __('ui.date') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($documents as $doc)
            <tr>
                <td><strong>{{ optional($doc->type)->name ?? $doc->title }}</strong></td>
                <td>{{ $doc->created_at->format('d M Y') }}</td>
                <td class="actions">
                    <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="btn btn-sm btn-ghost">{{ __('ui.view') }}</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
