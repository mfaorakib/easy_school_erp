@extends('builder::public.layout')

@section('body')
<section>
    <div class="wrap narrow" style="text-align:center">
        <div style="font-size:3.4rem;line-height:1;margin-bottom:1rem"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></div>
        <h2>Application Submitted!</h2>
        <p>Thank you for applying. Our admissions team will review your application and get back to you soon.</p>

        <div class="card" style="margin:2rem auto;max-width:420px">
            <div class="eyebrow">Your Reference Number</div>
            <div style="font-size:2rem;font-weight:800;letter-spacing:.03em;margin:.6rem 0;color:var(--primary)">{{ $application->reference_no }}</div>
            <p style="color:var(--muted);margin:0">Save this number — you'll need it to check your application status.</p>
        </div>

        <div style="display:flex;gap:.8rem;justify-content:center;flex-wrap:wrap;margin-top:1.5rem">
            <a href="{{ route('admission.status') }}" class="btn btn-ghost">Check Application Status</a>
            <a href="{{ url('/') }}" class="btn btn-ghost">Back to Home</a>
        </div>
    </div>
</section>
@endsection
