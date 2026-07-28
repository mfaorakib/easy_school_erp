@extends('layouts.admin')
@section('title', __('ui.exams'))

@section('content')
<div class="page-head"><h1>{{ $exam->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.exams') }}</h1>
    <a href="{{ route('exam.exams.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:560px">
    <form method="POST" action="{{ $exam->exists ? route('exam.exams.update', $exam) : route('exam.exams.store') }}">
        @csrf @if($exam->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.name') }} *</label><input name="name" value="{{ old('name', $exam->name) }}" required></div>
            <div><label>Type</label>
                <select name="exam_type_id">
                    <option value="">—</option>
                    @foreach($types as $t)<option value="{{ $t->id }}" {{ old('exam_type_id', $exam->exam_type_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach
                </select>
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $exam->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
