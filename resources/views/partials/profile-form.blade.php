<div class="card" style="max-width:520px">
    <h3 style="margin-top:0">{{ __('ui.profile_details') }}</h3>
    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PUT')

        <label>{{ __('ui.name') }}</label>
        <input name="name" value="{{ old('name', $user->name) }}" required>
        @error('name')<small class="badge">{{ $message }}</small>@enderror

        <label>{{ __('ui.email') }}</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}">
        @error('email')<small class="badge">{{ $message }}</small>@enderror

        <label>{{ __('ui.phone') }}</label>
        <input name="phone" value="{{ old('phone', $user->phone) }}">
        @error('phone')<small class="badge">{{ $message }}</small>@enderror

        <button type="submit" class="btn" style="margin-top:1.25rem">{{ __('ui.update_profile') }}</button>
    </form>
</div>

<div class="card" style="max-width:520px">
    <h3 style="margin-top:0">{{ __('ui.change_password') }}</h3>
    <form method="POST" action="{{ route('profile.password') }}">
        @csrf
        @method('PUT')

        <label>{{ __('ui.current_password') }}</label>
        <input type="password" name="current_password" autocomplete="current-password" required>
        @error('current_password')<small class="badge">{{ $message }}</small>@enderror

        <label>{{ __('ui.new_password') }}</label>
        <input type="password" name="password" autocomplete="new-password" required>
        @error('password')<small class="badge">{{ $message }}</small>@enderror

        <label>{{ __('ui.confirm_password') }}</label>
        <input type="password" name="password_confirmation" autocomplete="new-password" required>

        <button type="submit" class="btn" style="margin-top:1.25rem">{{ __('ui.change_password') }}</button>
    </form>
</div>
