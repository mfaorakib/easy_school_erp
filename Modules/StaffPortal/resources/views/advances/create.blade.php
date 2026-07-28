@extends('staffportal::layouts.portal')

@section('title', __('ui.salary_advance'))

@section('content')
    <div class="page-head">
        <h1>{{ __('ui.request_advance') }}</h1>
        <a href="{{ route('staffportal.advances.index') }}" class="btn btn-ghost">&larr; {{ __('ui.salary_advance') }}</a>
    </div>

    <div class="card" style="max-width:640px">
        <form method="POST" action="{{ route('staffportal.advances.store') }}">
            @csrf

            <div style="margin-bottom:1rem;">
                <label for="amount" style="display:block;font-weight:600;margin-bottom:.4rem;">{{ __('ui.amount') }}</label>
                <input
                    type="number"
                    id="amount"
                    name="amount"
                    step="0.01"
                    min="1"
                    value="{{ old('amount') }}"
                    required
                    style="width:100%;padding:.6rem .75rem;border:1px solid var(--border);border-radius:8px;background:var(--surface-2);color:var(--text);"
                >
                @error('amount')<small style="color:var(--danger);display:block;margin-top:.3rem;">{{ $message }}</small>@enderror
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
                <button type="submit" class="btn">{{ __('ui.request_advance') }}</button>
                <a href="{{ route('staffportal.advances.index') }}" class="btn btn-ghost">{{ __('ui.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
