@extends('layouts.admin')
@section('title', 'Online Exam')

@section('content')
<div class="page-head"><h1>{{ $exam->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.online_exam') }}</h1>
    <a href="{{ route('onlineexam.exams.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $exam->exists ? route('onlineexam.exams.update', $exam) : route('onlineexam.exams.store') }}">
        @csrf @if($exam->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.classes') }} *</label>
                <select name="class_id" required>
                    <option value="">—</option>
                    @foreach($classes as $c)<option value="{{ $c->id }}" {{ old('class_id', $exam->class_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div><label>{{ __('ui.sections') }} *</label>
                <select name="section_id" required>
                    <option value="">—</option>
                    @foreach($sections as $s)<option value="{{ $s->id }}" {{ old('section_id', $exam->section_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div><label>{{ __('ui.subjects') }}</label>
                <select name="subject_id">
                    <option value="">—</option>
                    @foreach($subjects as $sub)<option value="{{ $sub->id }}" {{ old('subject_id', $exam->subject_id) == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>@endforeach
                </select>
            </div>
            <div><label>{{ __('ui.name') }} *</label><input name="title" type="text" maxlength="255" value="{{ old('title', $exam->title) }}" required></div>
            <div><label>Date</label><input name="exam_date" type="date" value="{{ old('exam_date', optional($exam->exam_date)->format('Y-m-d')) }}"></div>
            <div><label>Duration (minutes)</label><input name="duration_minutes" type="number" min="0" value="{{ old('duration_minutes', $exam->duration_minutes) }}"></div>
            <div><label>Start time</label><input name="start_time" type="time" value="{{ old('start_time', $exam->start_time) }}"></div>
            <div><label>End time</label><input name="end_time" type="time" value="{{ old('end_time', $exam->end_time) }}"></div>
            <div style="grid-column:1/-1"><label>{{ __('ui.instruction') }}</label><textarea name="instruction" rows="4">{{ old('instruction', $exam->instruction) }}</textarea></div>
            <div><label><input type="checkbox" name="auto_mark" value="1" {{ old('auto_mark', $exam->exists ? $exam->auto_mark : true) ? 'checked' : '' }}> {{ __('ui.auto_mark') }}</label></div>
            <div><label><input type="checkbox" name="is_published" value="1" {{ old('is_published', $exam->is_published) ? 'checked' : '' }}> {{ __('ui.published') }}</label></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $exam->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
