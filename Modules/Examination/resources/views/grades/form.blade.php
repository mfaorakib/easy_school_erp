@extends('layouts.admin')
@section('title', __('ui.grades'))

@section('content')
<div class="page-head"><h1>{{ $grade->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.grades') }}</h1>
    <a href="{{ route('exam.grades.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:560px">
    <form method="POST" action="{{ $grade->exists ? route('exam.grades.update', $grade) : route('exam.grades.store') }}">
        @csrf @if($grade->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.grade') }} *</label><input name="name" value="{{ old('name', $grade->name) }}" required></div>
            <div><label>{{ __('ui.gpa') }} *</label><input name="gpa" type="number" step="any" value="{{ old('gpa', $grade->gpa) }}" required></div>
            <div><label>Mark from *</label><input name="mark_from" type="number" step="any" value="{{ old('mark_from', $grade->mark_from) }}" required></div>
            <div><label>Mark upto *</label><input name="mark_upto" type="number" step="any" value="{{ old('mark_upto', $grade->mark_upto) }}" required></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $grade->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
