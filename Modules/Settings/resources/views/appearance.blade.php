@extends('layouts.admin')
@section('title', __('ui.appearance'))

@section('content')
<div class="page-head"><h1>{{ __('ui.appearance') }}</h1></div>

<div class="card" style="max-width:680px">
    <form method="POST" action="{{ route('settings.appearance.update') }}">
        @csrf @method('PUT')
        <div class="grid">
            <div>
                <label>Primary color</label>
                <input type="color" name="admin_primary_color" value="{{ old('admin_primary_color', data_get($s, 'admin_primary_color') ?: '#4f46e5') }}">
                @error('admin_primary_color')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>Theme</label>
                <select name="admin_theme">
                    <option value="light" {{ old('admin_theme', data_get($s, 'admin_theme')) == 'light' ? 'selected' : '' }}>Light</option>
                    <option value="dark" {{ old('admin_theme', data_get($s, 'admin_theme')) == 'dark' ? 'selected' : '' }}>Dark</option>
                </select>
                @error('admin_theme')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ __('ui.save_settings') }}</button></div>
    </form>
</div>
@endsection
