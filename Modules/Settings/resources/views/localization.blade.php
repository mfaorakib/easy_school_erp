@extends('layouts.admin')
@section('title', 'Localization')

@section('content')
<div class="page-head"><h1>{{ __('ui.localization') }}</h1></div>

<div class="card" style="max-width:680px">
    <form method="POST" action="{{ route('settings.localization.update') }}">
        @csrf @method('PUT')
        <div class="grid">
            <div><label>{{ __('ui.timezone') }} *</label>
                <select name="timezone" required>
                    @foreach($timezones as $tz)<option value="{{ $tz }}" {{ old('timezone', data_get($s, 'timezone')) == $tz ? 'selected' : '' }}>{{ $tz }}</option>@endforeach
                </select>
                @error('timezone')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div><label>{{ __('ui.date_format') }} *</label>
                <select name="date_format" required>
                    @foreach($dateFormats as $fmt => $sample)<option value="{{ $fmt }}" {{ old('date_format', data_get($s, 'date_format')) == $fmt ? 'selected' : '' }}>{{ $fmt }} — {{ $sample }}</option>@endforeach
                </select>
                @error('date_format')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div><label>{{ __('ui.default_language') }} *</label>
                <select name="default_language" required>
                    @foreach($locales as $code => $meta)<option value="{{ $code }}" {{ old('default_language', data_get($s, 'default_language')) == $code ? 'selected' : '' }}>{{ $meta['name'] }}</option>@endforeach
                </select>
                @error('default_language')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div style="grid-column:1/-1"><label>{{ __('ui.weekend') }}</label>
                <div style="display:flex;flex-wrap:wrap;gap:1rem">
                    @foreach($weekDays as $day)
                        <label><input type="checkbox" name="weekend_days[]" value="{{ $day }}" {{ in_array($day, old('weekend_days', $weekend)) ? 'checked' : '' }}> {{ ucfirst($day) }}</label>
                    @endforeach
                </div>
                @error('weekend_days')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ __('ui.save_settings') }}</button></div>
    </form>
</div>
@endsection
