@extends('layouts.admin')
@section('title', 'Periods')

@section('content')
<div class="page-head"><h1>{{ $period->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.periods') }}</h1>
    <a href="{{ route('timetable.periods.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card max-w-full" style="max-width:680px">
    <form method="POST" action="{{ $period->exists ? route('timetable.periods.update', $period) : route('timetable.periods.store') }}">
        @csrf @if($period->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.period_name') }} *</label><input name="name" type="text" maxlength="100" value="{{ old('name', $period->name) }}" required></div>
            <div><label>{{ __('ui.start_time') }} *</label><input name="start_time" type="time" value="{{ old('start_time', $period->start_time ? substr($period->start_time, 0, 5) : '') }}" required></div>
            <div><label>{{ __('ui.end_time') }} *</label><input name="end_time" type="time" value="{{ old('end_time', $period->end_time ? substr($period->end_time, 0, 5) : '') }}" required></div>
            <div><label>Position</label><input name="position" type="number" min="0" value="{{ old('position', $period->position ?? 0) }}"></div>
            <div><label><input name="is_break" type="checkbox" value="1" {{ old('is_break', $period->is_break) ? 'checked' : '' }}> {{ __('ui.break') }}</label></div>
            <div><label><input name="is_active" type="checkbox" value="1" {{ old('is_active', $period->exists ? $period->is_active : true) ? 'checked' : '' }}> Active</label></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $period->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
