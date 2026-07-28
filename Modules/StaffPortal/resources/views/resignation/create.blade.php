@extends('staffportal::layouts.portal')

@section('title', __('ui.resignation'))

@section('content')
    <div class="page-head">
        <h1>{{ __('ui.submit_resignation') }}</h1>
        <a href="{{ route('staffportal.resignation.index') }}" class="btn btn-ghost">&larr; {{ __('ui.resignation') }}</a>
    </div>

    <div class="card" style="max-width:640px">
        <form method="POST" action="{{ route('staffportal.resignation.store') }}">
            @csrf

            <div style="margin-bottom:1rem;">
                <label for="intended_last_day" style="display:block;font-weight:600;margin-bottom:.4rem;">{{ __('ui.intended_last_day') }}</label>
                <input
                    type="date"
                    id="intended_last_day"
                    name="intended_last_day"
                    value="{{ old('intended_last_day') }}"
                    required
                    style="width:100%;padding:.6rem .75rem;border:1px solid var(--border);border-radius:8px;background:var(--surface-2);color:var(--text);"
                >
                @error('intended_last_day')<small style="color:var(--danger);display:block;margin-top:.3rem;">{{ $message }}</small>@enderror
            </div>

            <div style="margin-bottom:1rem;">
                <label for="reason" style="display:block;font-weight:600;margin-bottom:.4rem;">{{ __('ui.reason') }}</label>
                <textarea
                    id="reason"
                    name="reason"
                    rows="4"
                    maxlength="500"
                    style="width:100%;padding:.6rem .75rem;border:1px solid var(--border);border-radius:8px;background:var(--surface-2);color:var(--text);font-family:inherit;"
                >{{ old('reason') }}</textarea>
                @error('reason')<small style="color:var(--danger);display:block;margin-top:.3rem;">{{ $message }}</small>@enderror
            </div>

            <div style="display:flex;gap:.6rem;">
                <button type="submit" class="btn">{{ __('ui.submit_resignation') }}</button>
                <a href="{{ route('staffportal.resignation.index') }}" class="btn btn-ghost">{{ __('ui.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
