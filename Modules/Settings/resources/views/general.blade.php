@extends('layouts.admin')
@section('title', 'Settings')

@section('content')
<div class="page-head"><h1>{{ __('ui.general_settings') }} — {{ __('ui.settings') }}</h1></div>

<div class="card" style="max-width:680px">
    <form method="POST" action="{{ route('settings.general.update') }}">
        @csrf @method('PUT')
        <div class="grid">
            <div>
                <label>{{ __('ui.institution_name') }} *</label>
                <input name="school_name" type="text" maxlength="255" value="{{ old('school_name', data_get($s, 'school_name')) }}" required>
                @error('school_name')<small class="badge">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>Address</label>
                <input name="address" type="text" maxlength="255" value="{{ old('address', data_get($s, 'address')) }}">
                @error('address')<small class="badge">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>Phone</label>
                <input name="phone" type="text" maxlength="60" value="{{ old('phone', data_get($s, 'phone')) }}">
                @error('phone')<small class="badge">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>Email</label>
                <input name="email" type="email" maxlength="255" value="{{ old('email', data_get($s, 'email')) }}">
                @error('email')<small class="badge">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>{{ __('ui.currency') }} Code</label>
                <input name="currency_code" type="text" maxlength="10" placeholder="BDT" value="{{ old('currency_code', data_get($s, 'currency_code')) }}">
                @error('currency_code')<small class="badge">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>{{ __('ui.currency') }} Symbol</label>
                <input name="currency_symbol" type="text" maxlength="10" placeholder="৳" value="{{ old('currency_symbol', data_get($s, 'currency_symbol')) }}">
                @error('currency_symbol')<small class="badge">{{ $message }}</small>@enderror
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ __('ui.save_settings') }}</button></div>
    </form>
</div>
@endsection
