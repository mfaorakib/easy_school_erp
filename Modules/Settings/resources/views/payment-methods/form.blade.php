@extends('layouts.admin')
@section('title', __('ui.payment_methods'))

@section('content')
<div class="page-head"><h1>{{ $method->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.payment_methods') }}</h1>
    <a href="{{ route('settings.paymentMethods.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>

<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $method->exists ? route('settings.paymentMethods.update', $method) : route('settings.paymentMethods.store') }}">
        @csrf @if($method->exists) @method('PUT') @endif
        <div class="grid">
            <div>
                <label>{{ __('ui.name') }} *</label>
                <input name="name" type="text" maxlength="80" value="{{ old('name', $method->name) }}" required>
                @error('name')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>Position</label>
                <input name="position" type="number" min="0" value="{{ old('position', $method->position ?? 0) }}">
                @error('position')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $method->exists ? $method->is_active : true) ? 'checked' : '' }}>
                    Active
                </label>
            </div>
            <div>
                <label>{{ __('ui.gateway_type') }}</label>
                <select name="driver" id="driver-select">
                    <option value="manual" {{ old('driver', $method->driver ?? 'manual') === 'manual' ? 'selected' : '' }}>{{ __('ui.manual') }} / Offline</option>
                    <option value="stripe" {{ old('driver', $method->driver ?? 'manual') === 'stripe' ? 'selected' : '' }}>Stripe</option>
                    <option value="bkash" {{ old('driver', $method->driver ?? 'manual') === 'bkash' ? 'selected' : '' }}>bKash</option>
                    <option value="nagad" {{ old('driver', $method->driver ?? 'manual') === 'nagad' ? 'selected' : '' }}>Nagad</option>
                </select>
                @error('driver')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>
        </div>

        <div style="margin-top:1.25rem">
            <p class="badge" style="display:inline-block"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> Enter your real gateway credentials before going live — placeholder/demo values will not process real payments.</p>

            <div data-driver="manual" style="{{ old('driver', $method->driver ?? 'manual') === 'manual' ? '' : 'display:none' }};margin-top:1rem">
                <p class="muted">No configuration needed — staff/guardians record the transaction reference by hand.</p>
            </div>

            <div data-driver="stripe" style="{{ old('driver', $method->driver ?? 'manual') === 'stripe' ? '' : 'display:none' }};margin-top:1rem">
                <div class="grid">
                    <div>
                        <label>{{ __('ui.secret_key') }}</label>
                        <input name="secret_key" type="text" value="{{ old('secret_key', $method->config['secret_key'] ?? '') }}">
                    </div>
                    <div>
                        <label>Publishable Key</label>
                        <input name="publishable_key" type="text" value="{{ old('publishable_key', $method->config['publishable_key'] ?? '') }}">
                    </div>
                    <div>
                        <label>{{ __('ui.webhook') }} Secret</label>
                        <input name="webhook_secret" type="text" value="{{ old('webhook_secret', $method->config['webhook_secret'] ?? '') }}">
                    </div>
                    <div>
                        <label>Currency code</label>
                        <input name="currency" type="text" placeholder="usd" value="{{ old('currency', $method->config['currency'] ?? '') }}">
                        <small class="muted">Stripe does not support BDT as a presentment currency on most accounts — set the currency your Stripe account settles in.</small>
                    </div>
                </div>
            </div>

            <div data-driver="bkash" style="{{ old('driver', $method->driver ?? 'manual') === 'bkash' ? '' : 'display:none' }};margin-top:1rem">
                <div class="grid">
                    <div>
                        <label>App Key</label>
                        <input name="app_key" type="text" value="{{ old('app_key', $method->config['app_key'] ?? '') }}">
                    </div>
                    <div>
                        <label>App Secret</label>
                        <input name="app_secret" type="text" value="{{ old('app_secret', $method->config['app_secret'] ?? '') }}">
                    </div>
                    <div>
                        <label>Username</label>
                        <input name="username" type="text" value="{{ old('username', $method->config['username'] ?? '') }}">
                    </div>
                    <div>
                        <label>Password</label>
                        <input name="password" type="text" value="{{ old('password', $method->config['password'] ?? '') }}">
                    </div>
                    <div>
                        <label>
                            <input type="checkbox" name="sandbox" value="1" {{ old('sandbox', $method->config['sandbox'] ?? true) ? 'checked' : '' }}>
                            {{ __('ui.sandbox_mode') }}
                        </label>
                    </div>
                </div>
            </div>

            <div data-driver="nagad" style="{{ old('driver', $method->driver ?? 'manual') === 'nagad' ? '' : 'display:none' }};margin-top:1rem">
                <div class="grid">
                    <div>
                        <label>Merchant ID</label>
                        <input name="merchant_id" type="text" value="{{ old('merchant_id', $method->config['merchant_id'] ?? '') }}">
                    </div>
                    <div>
                        <label>Merchant Private Key</label>
                        <textarea name="merchant_private_key" rows="4">{{ old('merchant_private_key', $method->config['merchant_private_key'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label>PG Public Key</label>
                        <textarea name="pg_public_key" rows="4">{{ old('pg_public_key', $method->config['pg_public_key'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label>
                            <input type="checkbox" name="sandbox" value="1" {{ old('sandbox', $method->config['sandbox'] ?? true) ? 'checked' : '' }}>
                            {{ __('ui.sandbox_mode') }}
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top:1.25rem"><button class="btn">{{ $method->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var select = document.getElementById('driver-select');
    var blocks = document.querySelectorAll('[data-driver]');

    function syncDriverBlocks() {
        var value = select.value;
        blocks.forEach(function (block) {
            block.style.display = block.getAttribute('data-driver') === value ? '' : 'none';
        });
    }

    select.addEventListener('change', syncDriverBlocks);
});
</script>
@endsection
