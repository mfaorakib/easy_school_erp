@extends('layouts.admin')
@section('title', __('ui.site_settings'))

@section('content')
<div class="page-head">
    <h1>{{ __('ui.site_settings') }}</h1>
    <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-ghost">{{ __('ui.view_site') }}</a>
</div>

<form method="POST" action="{{ route('builder.settings.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="card" style="max-width:820px">
        <h2>{{ __('ui.branding') }}</h2>
        <div class="grid">
            <div style="grid-column:1/-1">
                <label>{{ __('ui.name') }} *</label>
                <input name="site_name" type="text" maxlength="255" value="{{ old('site_name', $settings->site_name) }}" required>
                @error('site_name')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div style="grid-column:1/-1">
                <label>Tagline</label>
                <input name="tagline" type="text" maxlength="255" value="{{ old('tagline', $settings->tagline) }}">
                @error('tagline')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div style="grid-column:1/-1">
                <label>{{ __('ui.logo') }}</label>
                @if($settings->logo_path)
                    <div style="margin-bottom:.5rem">
                        <img src="{{ \Modules\Builder\Services\BuilderService::media($settings->logo_path) }}" alt="{{ __('ui.logo') }}" style="max-height:80px">
                    </div>
                @endif
                <input type="file" name="logo" accept="image/*">
                @error('logo')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div style="grid-column:1/-1">
                <label>Footer text</label>
                <textarea name="footer_text" rows="3" maxlength="500">{{ old('footer_text', $settings->footer_text) }}</textarea>
                @error('footer_text')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>

    <div class="card" style="max-width:820px">
        <h2>Colors</h2>
        <div class="grid">
            <div>
                <label>{{ __('ui.primary_color') }} *</label>
                <input type="color" name="primary_color" value="{{ old('primary_color', $settings->primary_color) }}">
                @error('primary_color')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>{{ __('ui.secondary_color') }} *</label>
                <input type="color" name="secondary_color" value="{{ old('secondary_color', $settings->secondary_color) }}">
                @error('secondary_color')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>

    <div class="card" style="max-width:820px">
        <h2>Contact</h2>
        <div class="grid">
            <div>
                <label>Phone</label>
                <input name="phone" type="text" maxlength="60" value="{{ old('phone', $settings->phone) }}">
                @error('phone')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>Email</label>
                <input name="email" type="email" maxlength="255" value="{{ old('email', $settings->email) }}">
                @error('email')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div style="grid-column:1/-1">
                <label>Address</label>
                <input name="address" type="text" maxlength="255" value="{{ old('address', $settings->address) }}">
                @error('address')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>

    <div class="card" style="max-width:820px">
        <h2>{{ __('ui.social_links') }}</h2>
        <div class="grid">
            <div>
                <label>Facebook</label>
                <input name="facebook" type="text" maxlength="255" value="{{ old('facebook', $settings->facebook) }}">
                @error('facebook')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>Twitter</label>
                <input name="twitter" type="text" maxlength="255" value="{{ old('twitter', $settings->twitter) }}">
                @error('twitter')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>YouTube</label>
                <input name="youtube" type="text" maxlength="255" value="{{ old('youtube', $settings->youtube) }}">
                @error('youtube')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>LinkedIn</label>
                <input name="linkedin" type="text" maxlength="255" value="{{ old('linkedin', $settings->linkedin) }}">
                @error('linkedin')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>

    <div style="margin-top:1.25rem;max-width:820px">
        <button class="btn">{{ __('ui.save') }}</button>
    </div>
</form>
@endsection
