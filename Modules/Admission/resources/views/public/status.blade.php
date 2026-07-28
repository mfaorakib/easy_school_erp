@extends('builder::public.layout')

@section('body')
<style>
    .status-form .field label{display:block;font-weight:600;font-size:.9rem;margin-bottom:.35rem}
    .status-form .field input{width:100%;border:1px solid var(--line);border-radius:12px;padding:.7rem .9rem;font:inherit;background:#fff}
    .status-form .field input:focus{outline:none;border-color:var(--primary)}
    .status-form .submit-row{text-align:center;margin-top:1rem}
    .form-errors{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:.9rem 1.1rem;border-radius:12px;margin-bottom:1.5rem}
    .form-errors p{margin:.2rem 0}
    .status-result{margin-top:1.5rem}
    .status-result dl{display:grid;grid-template-columns:auto 1fr;gap:.5rem 1rem;margin:1rem 0}
    .status-result dt{font-weight:600;color:var(--muted)}
    .status-result dd{margin:0}
    .status-none{margin-top:1.5rem;color:var(--muted)}
    .back-link{display:inline-block;margin-top:2rem}
</style>

<section>
    <div class="wrap narrow">
        <div class="sec-head">
            <span class="eyebrow">Admissions</span>
            <h2>Check Your Application Status</h2>
        </div>

        @if($errors->any())
            <div class="form-errors">
                @foreach($errors->all() as $e)
                    <p>{{ $e }}</p>
                @endforeach
            </div>
        @endif

        <div class="card">
            <form class="status-form" method="POST" action="{{ route('admission.status.lookup') }}">
                @csrf
                <div class="field">
                    <label for="reference_no">Reference Number</label>
                    <input type="text" id="reference_no" name="reference_no" placeholder="e.g. APP-XXXXXXXX" value="{{ old('reference_no', $searched ?? '') }}">
                </div>
                <div class="submit-row">
                    <button type="submit" class="btn btn-primary">Check Status</button>
                </div>
            </form>
        </div>

        @isset($application)
            <div class="card status-result">
                <dl>
                    <dt>Applicant</dt>
                    <dd>{{ $application->first_name }} {{ $application->last_name }}</dd>

                    <dt>Reference No.</dt>
                    <dd>{{ $application->reference_no }}</dd>

                    <dt>Desired Class</dt>
                    <dd>{{ $application->desiredClass->name ?? '—' }}</dd>

                    <dt>Submitted</dt>
                    <dd>{{ $application->created_at->format('d M Y') }}</dd>

                    <dt>Status</dt>
                    <dd>
                        @if($application->status === \Modules\Admission\Models\AdmissionApplication::STATUS_PENDING)
                            <span style="display:inline-block;padding:.3rem .8rem;border-radius:999px;font-weight:600;font-size:.85rem;background:#fef3c7;color:#92400e">Pending Review</span>
                        @elseif($application->status === \Modules\Admission\Models\AdmissionApplication::STATUS_CONFIRMED)
                            <span style="display:inline-block;padding:.3rem .8rem;border-radius:999px;font-weight:600;font-size:.85rem;background:#dcfce7;color:#166534">Confirmed <i class="fa-solid fa-check" aria-hidden="true"></i></span>
                            <p style="margin:.75rem 0 0">Congratulations! Your application has been confirmed.</p>
                        @elseif($application->status === \Modules\Admission\Models\AdmissionApplication::STATUS_REJECTED)
                            <span style="display:inline-block;padding:.3rem .8rem;border-radius:999px;font-weight:600;font-size:.85rem;background:#fee2e2;color:#991b1b">Not Approved</span>
                            @if($application->rejection_reason)
                                <p style="margin:.75rem 0 0">Reason: {{ $application->rejection_reason }}</p>
                            @endif
                        @endif
                    </dd>
                </dl>
            </div>
        @elseif(isset($searched))
            <p class="status-none">No application found for reference number "{{ $searched }}". Please check and try again.</p>
        @endisset

        <a class="back-link" href="{{ url('/') }}">← Back to Home</a>
    </div>
</section>
@endsection
