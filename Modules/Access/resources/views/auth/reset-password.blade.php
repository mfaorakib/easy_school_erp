@extends('layouts.app')

@section('title', __('ui.reset_password'))

@section('content')
<div class="auth-card">
    <h1>{{ __('ui.reset_password') }}</h1>

    @if ($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <label>{{ __('ui.email') }}</label>
        <input type="email" name="email" value="{{ old('email', $email) }}" autofocus required>

        <label>{{ __('ui.new_password') }}</label>
        <input type="password" name="password" required>

        <label>{{ __('ui.confirm_password') }}</label>
        <input type="password" name="password_confirmation" required>

        <button type="submit">{{ __('ui.reset_password') }}</button>
    </form>
</div>
@endsection
