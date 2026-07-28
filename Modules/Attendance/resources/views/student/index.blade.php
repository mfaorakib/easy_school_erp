@extends('layouts.admin')
@section('title', __('ui.student_attendance'))

@section('content')
<div class="page-head"><h1>{{ __('ui.student_attendance') }}</h1></div>

{{-- Filter --}}
<div class="card">
    <form method="GET" action="{{ route('attendance.student.index') }}">
        <div class="grid">
            <div><label>{{ __('ui.classes') }}</label>
                <select name="class_id">
                    <option value="">—</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>{{ __('ui.sections') }}</label>
                <select name="section_id">
                    <option value="">—</option>
                    @foreach($sections as $s)
                        <option value="{{ $s->id }}" {{ $sectionId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>{{ __('ui.date') }}</label><input type="date" name="date" value="{{ $date }}"></div>
            <div style="display:flex;align-items:flex-end"><button class="btn">{{ __('ui.load') }}</button></div>
        </div>
    </form>
</div>

@if($classId && $sectionId)
<div class="card">
    @if($roster->isEmpty())
        <div class="empty">No live students in this class/section.</div>
    @else
    <form method="POST" action="{{ route('attendance.student.store') }}">
        @csrf
        <input type="hidden" name="class_id" value="{{ $classId }}">
        <input type="hidden" name="section_id" value="{{ $sectionId }}">
        <input type="hidden" name="date" value="{{ $date }}">

        <div style="margin-bottom:.75rem">
            <button type="button" class="btn btn-sm btn-ghost"
                onclick="document.querySelectorAll('select[data-att]').forEach(s=>s.value='P')">{{ __('ui.present_all') }}</button>
        </div>

        <div class="overflow-x-auto">
        <table>
            <thead><tr><th>Roll</th><th>{{ __('ui.name') }}</th><th>{{ __('ui.status') }}</th><th>Note</th></tr></thead>
            <tbody>
            @foreach($roster as $row)
                <tr>
                    <td>{{ $row['roll_no'] ?? '—' }}</td>
                    <td>{{ $row['student']->full_name }}</td>
                    <td>
                        <select name="status[{{ $row['student']->id }}]" data-att>
                            @foreach($statuses as $st)
                                <option value="{{ $st->value }}" {{ ($row['status'] ?? 'P') === $st->value ? 'selected' : '' }}>{{ $st->label() }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input name="note[{{ $row['student']->id }}]" value="{{ $row['note'] }}" placeholder="—"></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>

        <div style="margin-top:1.25rem"><button class="btn">{{ __('ui.save') }}</button></div>
    </form>
    @endif
</div>
@endif
@endsection
