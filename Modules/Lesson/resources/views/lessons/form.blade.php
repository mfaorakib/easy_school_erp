@extends('layouts.admin')
@section('title', 'Lessons')

@section('content')
<div class="page-head"><h1>{{ $lesson->exists ? __('ui.edit') : __('ui.add') }} — Lesson</h1>
    <a href="{{ route('lesson.lessons.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $lesson->exists ? route('lesson.lessons.update', $lesson) : route('lesson.lessons.store') }}">
        @csrf @if($lesson->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.classes') }} *</label>
                <select name="class_id" required>
                    <option value="">—</option>
                    @foreach($classes as $c)<option value="{{ $c->id }}" {{ old('class_id', $lesson->class_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div><label>{{ __('ui.sections') }} *</label>
                <select name="section_id" required>
                    <option value="">—</option>
                    @foreach($sections as $s)<option value="{{ $s->id }}" {{ old('section_id', $lesson->section_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div><label>{{ __('ui.subjects') }} *</label>
                <select name="subject_id" required>
                    <option value="">—</option>
                    @foreach($subjects as $sub)<option value="{{ $sub->id }}" {{ old('subject_id', $lesson->subject_id) == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>@endforeach
                </select>
            </div>
            <div><label>Teacher (optional)</label>
                <select name="teacher_id">
                    <option value="">—</option>
                    @foreach($teachers as $t)<option value="{{ $t->id }}" {{ old('teacher_id', $lesson->teacher_id) == $t->id ? 'selected' : '' }}>{{ $t->full_name ?: optional($t->user)->name }}</option>@endforeach
                </select>
            </div>
            <div><label>{{ __('ui.name') }} *</label><input name="title" type="text" maxlength="200" value="{{ old('title', $lesson->title) }}" required></div>
            <div><label>Date</label><input name="lesson_date" type="date" value="{{ old('lesson_date', optional($lesson->lesson_date)->format('Y-m-d')) }}"></div>
            <div style="grid-column:1/-1"><label>Description</label><textarea name="description" rows="4">{{ old('description', $lesson->description) }}</textarea></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $lesson->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
