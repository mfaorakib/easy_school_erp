@extends('layouts.app')

@section('title', __('ui.forgot_password'))

@section('content')
<div class="auth-card">
    <h1>{{ __('ui.forgot_password') }}</h1>
    <p class="muted">{{ __('ui.reset_link_identifier') }}</p>

    @if (session('status'))
        <div class="alert" style="background:#14532d;color:#bbf7d0">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <label>{{ __('ui.email') }} / {{ __('ui.username') }} / {{ __('ui.phone') }}</label>
        <input name="identifier" value="{{ old('identifier') }}" autofocus required>

        <button type="submit">{{ __('ui.send_reset_link') }}</button>
    </form>

    <p style="margin-top:1.25rem"><a href="{{ route('login') }}" class="logout">{{ __('ui.back_to_login') }}</a></p>
</div>
@endsection
