@extends('layouts.admin')
@section('title', __('ui.subject_assign'))

@section('content')
<div class="page-head"><h1>{{ __('ui.subject_assign') }}</h1></div>

{{-- Filter --}}
<div class="card">
    <form method="GET" action="{{ route('academic.subject-assignments.index') }}">
        <div class="grid">
            <div><label>Class</label>
                <select name="class_id" onchange="this.form.submit()">
                    <option value="">—</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>Section (blank = all sections)</label>
                <select name="section_id" onchange="this.form.submit()">
                    <option value="">All sections</option>
                    @foreach($sections as $s)
                        <option value="{{ $s->id }}" {{ $sectionId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>
</div>

@if($classId)
<div class="card">
    <h3 style="margin-top:0">Assign subjects</h3>
    <form method="POST" action="{{ route('academic.subject-assignments.store') }}">
        @csrf
        <input type="hidden" name="class_id" value="{{ $classId }}">
        <input type="hidden" name="section_id" value="{{ $sectionId }}">
        <div class="overflow-x-auto">
        <table>
            <thead><tr><th></th><th>Subject</th><th>Teacher</th></tr></thead>
            <tbody>
            @foreach($subjects as $subject)
                <tr>
                    <td><input type="checkbox" name="subjects[]" value="{{ $subject->id }}" style="width:auto"></td>
                    <td>{{ $subject->name }} <span class="badge">{{ ucfirst($subject->type) }}</span></td>
                    <td>
                        <select name="teachers[{{ $subject->id }}]">
                            <option value="">— {{ $teachers->isEmpty() ? 'no teachers yet' : 'unassigned' }} —</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->full_name ?? $t->user->name }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <div style="margin-top:1rem"><button class="btn">{{ __('ui.assign') }}</button></div>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0">Current assignments</h3>
    @if($existing->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Section</th><th>Subject</th><th>Teacher</th></tr></thead>
        <tbody>
        @foreach($existing as $a)
            <tr>
                <td>{{ optional($a->section)->name }}</td>
                <td>{{ optional($a->subject)->name }}</td>
                <td>{{ optional($a->teacher)->full_name ?? '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endif
@endsection
