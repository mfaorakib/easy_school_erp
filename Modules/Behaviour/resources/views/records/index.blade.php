@extends('layouts.admin')
@section('title', 'Behaviour records')

@section('content')
<div class="page-head"><h1>Behaviour records</h1></div>

<div class="card">
    <h3 style="margin-top:0">Record behaviour</h3>
    @if($students->isEmpty())
        <div class="empty">No active students available yet.</div>
    @elseif($types->isEmpty())
        <div class="empty">No active behaviour types available yet. Add a behaviour type first.</div>
    @else
    <form method="POST" action="{{ route('behaviour.records.store') }}">
        @csrf
        <div class="grid">
            <div><label>Student *</label>
                <select name="student_id" required>
                    <option value="">—</option>
                    @foreach($students as $s)
                        @php($rec = $s->liveRecord->first())
                        @php($className = optional(optional($rec)->schoolClass)->name)
                        @php($sectionName = optional(optional($rec)->section)->name)
                        <option value="{{ $s->id }}">
                            {{ $s->full_name }} — {{ $className ?? '—' }}{{ $sectionName ? '/'.$sectionName : '' }} — Roll {{ $rec->roll_no ?? '—' }} — {{ $s->admission_no }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div><label>Behaviour type *</label>
                <select name="behaviour_type_id" required>
                    <option value="">—</option>
                    @foreach($types as $t)
                        <option value="{{ $t->id }}">{{ $t->title }} ({{ $t->point }})</option>
                    @endforeach
                </select>
            </div>
            <div><label>Date *</label>
                <input type="date" name="incident_date" value="{{ old('incident_date', now()->toDateString()) }}" required>
            </div>
            <div><label>Comment</label>
                <input type="text" name="comment" maxlength="255" value="{{ old('comment') }}">
            </div>
        </div>
        <div style="margin-top:1rem"><button class="btn">{{ __('ui.save') }}</button></div>
    </form>
    @endif
</div>

<div class="card">
    <h3 style="margin-top:0">Recorded behaviour</h3>
    @if($records->isEmpty())
        <div class="empty">No behaviour recorded yet.</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Date</th><th>Student</th><th>Class</th><th>Section</th><th>Roll</th><th>ID</th><th>Type</th><th>Points</th><th>Comment</th><th></th></tr></thead>
        <tbody>
        @foreach($records as $r)
            @php($rec = optional($r->student)->liveRecord?->first())
            <tr>
                <td>{{ $r->incident_date?->format('d M Y') }}</td>
                <td>{{ $r->student?->full_name }}</td>
                <td>{{ optional(optional($rec)->schoolClass)->name ?? '—' }}</td>
                <td>{{ optional(optional($rec)->section)->name ?? '—' }}</td>
                <td>{{ $rec->roll_no ?? '—' }}</td>
                <td>{{ $r->student?->admission_no }}</td>
                <td>{{ $r->type?->title }}</td>
                <td>
                    <span class="badge" style="color:var(--{{ $r->points >= 0 ? 'success' : 'danger' }})">{{ $r->points }}</span>
                </td>
                <td>{{ $r->comment }}</td>
                <td>
                    <form method="POST" action="{{ route('behaviour.records.destroy', $r) }}" onsubmit="return confirm('Remove this record?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
