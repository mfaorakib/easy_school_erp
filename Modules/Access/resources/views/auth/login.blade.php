@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="auth-card">
    <h1>EasySchool ERP</h1>
    <p class="muted">Sign in to continue</p>

    @if ($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <label>Email, username or phone</label>
        <input name="identifier" value="{{ old('identifier') }}" autofocus required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label class="check">
            <input type="checkbox" name="remember" value="1"> Remember me
        </label>

        <button type="submit">Sign in</button>
    </form>

    <p style="margin-top:1.25rem;text-align:center"><a href="{{ route('password.request') }}" class="logout">{{ __('ui.forgot_password') }}</a></p>
</div>
@endsection
