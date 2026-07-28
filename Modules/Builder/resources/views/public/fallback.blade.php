@extends('builder::public.layout')

@section('body')
    <section class="hero">
        <div class="wrap">
            <h1>{{ $settings->site_name }}</h1>
            <p>{{ $settings->tagline ?: 'Your school website is ready to be designed from the admin Builder.' }}</p>
            <div class="actions">
                <a class="btn btn-ghost" href="{{ route('login') }}">Login →</a>
            </div>
        </div>
    </section>
    <section>
        <div class="wrap prose" style="text-align:center">
            <span class="eyebrow">Builder</span>
            <h2>No home page published yet</h2>
            <p>Sign in and open <strong>Builder → Pages</strong> to compose your public home page from content blocks.</p>
        </div>
    </section>
@endsection
