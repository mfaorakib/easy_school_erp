@extends('layouts.admin')
@section('title', 'Classrooms')

@section('content')
<div class="page-head"><h1>{{ $classroom->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.classrooms') }}</h1>
    <a href="{{ route('timetable.classrooms.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card max-w-full" style="max-width:680px">
    <form method="POST" action="{{ $classroom->exists ? route('timetable.classrooms.update', $classroom) : route('timetable.classrooms.store') }}">
        @csrf @if($classroom->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.room_no') }} *</label><input name="room_no" type="text" maxlength="50" value="{{ old('room_no', $classroom->room_no) }}" required></div>
            <div><label>{{ __('ui.capacity') }}</label><input name="capacity" type="number" min="0" value="{{ old('capacity', $classroom->capacity) }}"></div>
            <div><label><input name="is_active" type="checkbox" value="1" {{ old('is_active', $classroom->exists ? $classroom->is_active : true) ? 'checked' : '' }}> Active</label></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $classroom->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
