@extends('layouts.admin')
@section('title', 'Homework')

@section('content')
<div class="page-head"><h1>{{ $homework->exists ? __('ui.edit') : __('ui.add') }} — Homework</h1>
    <a href="{{ route('homework.homeworks.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $homework->exists ? route('homework.homeworks.update', $homework) : route('homework.homeworks.store') }}">
        @csrf @if($homework->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.classes') }} *</label>
                <select name="class_id" required>
                    <option value="">—</option>
                    @foreach($classes as $c)<option value="{{ $c->id }}" {{ old('class_id', $homework->class_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div><label>{{ __('ui.sections') }} *</label>
                <select name="section_id" required>
                    <option value="">—</option>
                    @foreach($sections as $s)<option value="{{ $s->id }}" {{ old('section_id', $homework->section_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div><label>{{ __('ui.subjects') }} *</label>
                <select name="subject_id" required>
                    <option value="">—</option>
                    @foreach($subjects as $sub)<option value="{{ $sub->id }}" {{ old('subject_id', $homework->subject_id) == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>@endforeach
                </select>
            </div>
            <div><label>Teacher (optional)</label>
                <select name="teacher_id">
                    <option value="">—</option>
                    @foreach($teachers as $t)<option value="{{ $t->id }}" {{ old('teacher_id', $homework->teacher_id) == $t->id ? 'selected' : '' }}>{{ $t->full_name ?: optional($t->user)->name }}</option>@endforeach
                </select>
            </div>
            <div><label>{{ __('ui.name') }} *</label><input name="title" type="text" maxlength="200" value="{{ old('title', $homework->title) }}" required></div>
            <div><label>Homework date *</label><input name="homework_date" type="date" value="{{ old('homework_date', optional($homework->homework_date)->format('Y-m-d')) }}" required></div>
            <div><label>Submission date *</label><input name="submission_date" type="date" value="{{ old('submission_date', optional($homework->submission_date)->format('Y-m-d')) }}" required></div>
            <div><label>Evaluation marks</label><input name="evaluation_marks" type="number" step="any" min="0" value="{{ old('evaluation_marks', $homework->evaluation_marks) }}"></div>
            <div style="grid-column:1/-1"><label>Description</label><textarea name="description" rows="4">{{ old('description', $homework->description) }}</textarea></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $homework->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
