@extends('layouts.admin')
@section('title', __('ui.assign_role'))

@section('content')
<div class="page-head">
    <h1>{{ __('ui.assign_role') }} &mdash; {{ $user->name }}</h1>
    <a href="{{ route('access.users.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a>
</div>

<div class="card" style="max-width:480px">
    <p><strong>{{ __('ui.email') }}:</strong> {{ $user->email }}</p>
    <p><strong>{{ __('ui.username') }}:</strong> {{ $user->username }}</p>
</div>

<div class="card" style="max-width:480px">
    <form method="POST" action="{{ route('access.users.update', $user) }}">
        @csrf
        @method('PUT')

        <h3>{{ __('ui.roles') }}</h3>

        @foreach($allRoles as $role)
            <label style="display:flex;align-items:center;gap:.5rem;padding:.35rem 0">
                <input type="checkbox" name="roles[]" value="{{ $role->name }}" {{ $user->roles->contains('name', $role->name) ? 'checked' : '' }}>
                <span>{{ ucwords(str_replace('-', ' ', $role->name)) }}</span>
            </label>
        @endforeach

        <h3>{{ __('ui.reset_user_password') }}</h3>
        <label>{{ __('ui.new_password') }}</label>
        <input type="password" name="password" autocomplete="new-password">
        @error('password')<small class="badge">{{ $message }}</small>@enderror

        <label>{{ __('ui.confirm_password') }}</label>
        <input type="password" name="password_confirmation" autocomplete="new-password">
        <p class="l" style="color:var(--muted);margin-top:.35rem">{{ __('ui.leave_blank_to_keep') }}</p>

        <button type="submit" class="btn" style="margin-top:1.25rem">{{ __('ui.save') }}</button>
    </form>
</div>
@endsection
