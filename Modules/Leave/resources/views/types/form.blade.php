@extends('layouts.admin')
@section('title', 'Leave Types')

@section('content')
<div class="page-head"><h1>{{ $type->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.leave_types') }}</h1>
    <a href="{{ route('leave.types.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $type->exists ? route('leave.types.update', $type) : route('leave.types.store') }}">
        @csrf @if($type->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.name') }} *</label>
                <input name="name" type="text" maxlength="100" value="{{ old('name', $type->name) }}" required>
                @error('name')<div class="badge btn-danger">{{ $message }}</div>@enderror
            </div>
            <div><label>{{ __('ui.days_allowed') }} *</label>
                <input name="days_allowed" type="number" min="0" value="{{ old('days_allowed', $type->days_allowed ?? 0) }}" required>
                @error('days_allowed')<div class="badge btn-danger">{{ $message }}</div>@enderror
            </div>
            <div style="grid-column:1/-1">
                <label><input name="is_active" type="checkbox" value="1" {{ old('is_active', $type->exists ? $type->is_active : true) ? 'checked' : '' }}> Active</label>
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $type->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
