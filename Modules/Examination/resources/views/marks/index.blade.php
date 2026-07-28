@extends('layouts.admin')
@section('title', __('ui.marks_entry'))

@section('content')
<div class="page-head"><h1>{{ __('ui.marks_entry') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('exam.marks.index') }}">
        <div class="grid">
            <div><label>{{ __('ui.exams') }}</label>
                <select name="exam_id" onchange="this.form.submit()"><option value="">—</option>
                    @foreach($exams as $e)<option value="{{ $e->id }}" {{ $examId == $e->id ? 'selected' : '' }}>{{ $e->name }}</option>@endforeach
                </select></div>
            <div><label>{{ __('ui.classes') }}</label>
                <select name="class_id" onchange="this.form.submit()"><option value="">—</option>
                    @foreach($classes as $c)<option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                </select></div>
            <div><label>{{ __('ui.sections') }}</label>
                <select name="section_id" onchange="this.form.submit()"><option value="">—</option>
                    @foreach($sections as $s)<option value="{{ $s->id }}" {{ $sectionId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
                </select></div>
            <div><label>{{ __('ui.subjects') }}</label>
                <select name="subject_id" onchange="this.form.submit()"><option value="">—</option>
                    @foreach($subjects as $sch)<option value="{{ $sch->subject_id }}" {{ $subjectId == $sch->subject_id ? 'selected' : '' }}>{{ optional($sch->subject)->name }}</option>@endforeach
                </select></div>
        </div>
    </form>
</div>

@if($subjectId && $schedule)
<div class="card">
    <p class="empty" style="text-align:left;color:var(--muted);padding:0 0 .5rem">Full mark: {{ (int) $schedule->full_mark }} · Pass mark: {{ (int) $schedule->pass_mark }}</p>
    @if(empty($roster))<div class="empty">No live students.</div>@else
    <form method="POST" action="{{ route('exam.marks.store') }}">
        @csrf
        <input type="hidden" name="exam_id" value="{{ $examId }}">
        <input type="hidden" name="class_id" value="{{ $classId }}">
        <input type="hidden" name="section_id" value="{{ $sectionId }}">
        <input type="hidden" name="subject_id" value="{{ $subjectId }}">
        <div class="overflow-x-auto">
        <table>
            <thead><tr><th>Roll</th><th>{{ __('ui.name') }}</th><th>Marks</th><th>{{ __('ui.absent') }}</th></tr></thead>
            <tbody>
            @foreach($roster as $row)
                <tr>
                    <td>{{ $row['roll_no'] ?? '—' }}</td>
                    <td>{{ $row['student']->full_name }}</td>
                    <td><input type="number" step="any" name="marks[{{ $row['student']->id }}]" value="{{ $row['marks'] }}" style="width:100px" max="{{ $schedule->full_mark }}"></td>
                    <td><input type="checkbox" name="absent[{{ $row['student']->id }}]" value="1" {{ $row['absent'] ? 'checked' : '' }} style="width:auto"></td>
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
