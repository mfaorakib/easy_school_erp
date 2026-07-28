@extends('layouts.admin')
@section('title', 'SMS Settings')

@section('content')
<div class="page-head"><h1>{{ __('ui.sms_settings') }}</h1></div>

<div class="card" style="max-width:680px">
    <form method="POST" action="{{ route('settings.sms.update') }}">
        @csrf @method('PUT')
        <div class="grid">
            <div><label>Provider</label>
                <input name="sms_provider" type="text" maxlength="60" placeholder="e.g. Twilio / none" value="{{ old('sms_provider', data_get($s, 'sms_provider')) }}">
                @error('sms_provider')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div><label>API Key</label>
                <input name="sms_api_key" type="text" maxlength="255" value="{{ old('sms_api_key', data_get($s, 'sms_api_key')) }}">
                @error('sms_api_key')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div><label>Sender ID</label>
                <input name="sms_sender_id" type="text" maxlength="60" value="{{ old('sms_sender_id', data_get($s, 'sms_sender_id')) }}">
                @error('sms_sender_id')<span class="error">{{ $message }}</span>@enderror
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ __('ui.save_settings') }}</button></div>
    </form>
</div>
@endsection
