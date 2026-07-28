@extends('layouts.admin')
@section('title', __('ui.exam_schedule'))

@section('content')
<div class="page-head"><h1>{{ __('ui.exam_schedule') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('exam.schedules.index') }}">
        <div class="grid">
            <div><label>{{ __('ui.exams') }}</label>
                <select name="exam_id"><option value="">—</option>
                    @foreach($exams as $e)<option value="{{ $e->id }}" {{ $examId == $e->id ? 'selected' : '' }}>{{ $e->name }}</option>@endforeach
                </select></div>
            <div><label>{{ __('ui.classes') }}</label>
                <select name="class_id"><option value="">—</option>
                    @foreach($classes as $c)<option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                </select></div>
            <div><label>{{ __('ui.sections') }}</label>
                <select name="section_id"><option value="">—</option>
                    @foreach($sections as $s)<option value="{{ $s->id }}" {{ $sectionId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
                </select></div>
            <div style="display:flex;align-items:flex-end"><button class="btn">{{ __('ui.load') }}</button></div>
        </div>
    </form>
</div>

@if($subjects->isNotEmpty())
<div class="card">
    <form method="POST" action="{{ route('exam.schedules.store') }}">
        @csrf
        <input type="hidden" name="exam_id" value="{{ $examId }}">
        <input type="hidden" name="class_id" value="{{ $classId }}">
        <input type="hidden" name="section_id" value="{{ $sectionId }}">
        <div class="overflow-x-auto">
        <table>
            <thead><tr><th>Include</th><th>{{ __('ui.subjects') }}</th><th>{{ __('ui.date') }}</th><th>Full mark</th><th>Pass mark</th></tr></thead>
            <tbody>
            @foreach($subjects as $subject)
                @php($sch = $existing->get($subject->id))
                <tr>
                    <td><input type="checkbox" name="include[{{ $subject->id }}]" value="1" {{ $sch ? 'checked' : '' }} style="width:auto"></td>
                    <td>{{ $subject->name }}</td>
                    <td><input type="date" name="exam_date[{{ $subject->id }}]" value="{{ optional(optional($sch)->exam_date)->format('Y-m-d') }}"></td>
                    <td><input type="number" step="any" name="full_mark[{{ $subject->id }}]" value="{{ $sch->full_mark ?? 100 }}"></td>
                    <td><input type="number" step="any" name="pass_mark[{{ $subject->id }}]" value="{{ $sch->pass_mark ?? 33 }}"></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ __('ui.save') }}</button></div>
    </form>
</div>
@endif
@endsection
