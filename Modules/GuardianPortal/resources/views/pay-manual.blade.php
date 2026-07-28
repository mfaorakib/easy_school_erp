@extends('guardianportal::layouts.portal')

@section('title', __('ui.instructions'))

@section('content')
    <div class="wrap" style="max-width:520px;margin:0 auto;">
        <div class="card">
            <h1>{{ __('ui.instructions') }}</h1>
            <p>
                You selected <strong>{{ $intent->method->name }}</strong>. Please complete the payment using
                that method, then enter your transaction reference number below so we can confirm it.
            </p>

            <p>
                {{ __('ui.amount') }}: <strong>{{ number_format($intent->amount, 2) }}</strong><br>
                {{ __('ui.name') }}: {{ $intent->assignment->master->type->name ?? '—' }}
            </p>

            <form method="POST" action="{{ route('portal.pay.manual.submit', $intent->token) }}">
                @csrf
                <div style="margin-bottom:1rem;">
                    <label for="reference" style="display:block;font-weight:600;margin-bottom:.4rem;">{{ __('ui.transaction_reference') }}</label>
                    <input
                        type="text"
                        id="reference"
                        name="reference"
                        value="{{ old('reference') }}"
                        required
                        style="width:100%;padding:.6rem .75rem;border:1px solid var(--border);border-radius:8px;background:var(--surface-2);color:var(--text);"
                    >
                </div>

                <div style="display:flex;gap:.6rem;">
                    <button type="submit" class="btn">{{ __('ui.submit') }}</button>
                    <a href="{{ route('portal.dashboard') }}" class="btn btn-ghost">{{ __('ui.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
