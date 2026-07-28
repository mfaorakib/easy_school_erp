@extends('layouts.admin')
@section('title', 'Email Settings')

@section('content')
<div class="page-head"><h1>{{ __('ui.email_settings') }}</h1></div>

<div class="card" style="max-width:680px">
    <form method="POST" action="{{ route('settings.email.update') }}">
        @csrf @method('PUT')
        <div class="grid">
            <div><label>SMTP Host</label>
                <input name="mail_host" type="text" maxlength="255" value="{{ old('mail_host', data_get($s, 'mail_host')) }}">
                @error('mail_host')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div><label>Port</label>
                <input name="mail_port" type="text" maxlength="10" value="{{ old('mail_port', data_get($s, 'mail_port')) }}">
                @error('mail_port')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div><label>Username</label>
                <input name="mail_username" type="text" maxlength="255" value="{{ old('mail_username', data_get($s, 'mail_username')) }}">
                @error('mail_username')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div><label>Password</label>
                <input name="mail_password" type="password" maxlength="255" value="{{ old('mail_password', data_get($s, 'mail_password')) }}">
                @error('mail_password')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div><label>Encryption</label>
                <select name="mail_encryption">
                    @php $enc = old('mail_encryption', data_get($s, 'mail_encryption')); @endphp
                    <option value="tls" {{ $enc === 'tls' ? 'selected' : '' }}>tls</option>
                    <option value="ssl" {{ $enc === 'ssl' ? 'selected' : '' }}>ssl</option>
                    <option value="none" {{ $enc === 'none' ? 'selected' : '' }}>none</option>
                </select>
                @error('mail_encryption')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div><label>From Address</label>
                <input name="mail_from_address" type="email" maxlength="255" value="{{ old('mail_from_address', data_get($s, 'mail_from_address')) }}">
                @error('mail_from_address')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div><label>From Name</label>
                <input name="mail_from_name" type="text" maxlength="255" value="{{ old('mail_from_name', data_get($s, 'mail_from_name')) }}">
                @error('mail_from_name')<span class="error">{{ $message }}</span>@enderror
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ __('ui.save_settings') }}</button></div>
    </form>
</div>
@endsection
