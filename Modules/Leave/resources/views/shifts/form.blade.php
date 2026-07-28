@extends('layouts.admin')
@section('title', 'Shifts')

@section('content')
<div class="page-head"><h1>{{ $shift->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.shifts') }}</h1>
    <a href="{{ route('leave.shifts.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $shift->exists ? route('leave.shifts.update', $shift) : route('leave.shifts.store') }}">
        @csrf @if($shift->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.name') }} *</label>
                <input name="name" type="text" maxlength="100" value="{{ old('name', $shift->name) }}" required>
                @error('name')<span class="badge badge-danger">{{ $message }}</span>@enderror
            </div>
            <div><label>{{ __('ui.start_time') }} *</label>
                <input name="start_time" type="time" value="{{ old('start_time', $shift->start_time ? substr($shift->start_time, 0, 5) : '') }}" required>
                @error('start_time')<span class="badge badge-danger">{{ $message }}</span>@enderror
            </div>
            <div><label>{{ __('ui.end_time') }} *</label>
                <input name="end_time" type="time" value="{{ old('end_time', $shift->end_time ? substr($shift->end_time, 0, 5) : '') }}" required>
                @error('end_time')<span class="badge badge-danger">{{ $message }}</span>@enderror
            </div>
            <div><label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $shift->exists ? $shift->is_active : true) ? 'checked' : '' }}> Active</label></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $shift->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
