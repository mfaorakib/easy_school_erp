@extends('layouts.admin')
@section('title', 'Complaint Types')

@section('content')
<div class="page-head"><h1>{{ $type->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.complaint_types') }}</h1>
    <a href="{{ route('frontoffice.complaintTypes.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $type->exists ? route('frontoffice.complaintTypes.update', $type) : route('frontoffice.complaintTypes.store') }}">
        @csrf @if($type->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.name') }} *</label>
                <input name="name" type="text" maxlength="150" value="{{ old('name', $type->name) }}" required>
                @error('name')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div><label>
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $type->exists ? $type->is_active : true) ? 'checked' : '' }}>
                {{ __('ui.active') }}
            </label></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $type->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
