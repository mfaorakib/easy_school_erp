@extends('builder::public.layout')

@section('body')
<style>
    .apply-form .field label{display:block;font-weight:600;font-size:.9rem;margin-bottom:.35rem}
    .apply-form .field input,
    .apply-form .field select,
    .apply-form .field textarea{width:100%;border:1px solid var(--line);border-radius:12px;padding:.7rem .9rem;font:inherit;background:#fff}
    .apply-form .field input:focus,
    .apply-form .field select:focus,
    .apply-form .field textarea:focus{outline:none;border-color:var(--primary)}
    .apply-form .field small{display:block;color:var(--muted);margin-top:.35rem;font-size:.82rem}
    .apply-form .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
    .apply-form .full{grid-column:1/-1}
    .apply-form .submit-row{grid-column:1/-1;text-align:center;margin-top:.5rem}
    .form-errors{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:.9rem 1.1rem;border-radius:12px;margin-bottom:1.5rem}
    .form-errors p{margin:.2rem 0}
    @media(max-width:700px){
        .apply-form .grid-2{grid-template-columns:1fr}
    }
</style>

<section>
    <div class="wrap narrow">
        <div class="sec-head">
            <span class="eyebrow">Admissions</span>
            <h2>Apply for Admission</h2>
            <p>Fill in the form below to start your application. It only takes a few minutes, and you can track your status anytime with your reference number.</p>
        </div>

        @if($errors->any())
            <div class="form-errors">
                @foreach($errors->all() as $e)
                    <p>{{ $e }}</p>
                @endforeach
            </div>
        @endif

        <div class="card">
            <form class="apply-form" method="POST" action="{{ route('admission.apply.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid-2">
                    <div class="field">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                    </div>
                    <div class="field">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}">
                    </div>

                    <div class="field">
                        <label for="gender_id">Gender</label>
                        <select id="gender_id" name="gender_id">
                            <option value="">—</option>
                            @foreach($genders as $gender)
                                <option value="{{ $gender->id }}" @selected(old('gender_id') == $gender->id)>{{ $gender->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="date_of_birth">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                    </div>

                    <div class="field full">
                        <label for="desired_class_id">Desired Class *</label>
                        <select id="desired_class_id" name="desired_class_id" required>
                            <option value="">Select a class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('desired_class_id') == $class->id)>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="guardian_name">Guardian Name</label>
                        <input type="text" id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}">
                    </div>
                    <div class="field">
                        <label for="guardian_relation">Guardian Relation</label>
                        <input type="text" id="guardian_relation" name="guardian_relation" value="{{ old('guardian_relation') }}" placeholder="Father / Mother / Guardian">
                    </div>

                    <div class="field">
                        <label for="guardian_mobile">Guardian Mobile</label>
                        <input type="text" id="guardian_mobile" name="guardian_mobile" value="{{ old('guardian_mobile') }}">
                    </div>
                    <div class="field">
                        <label for="guardian_email">Guardian Email</label>
                        <input type="email" id="guardian_email" name="guardian_email" value="{{ old('guardian_email') }}">
                    </div>

                    <div class="field full">
                        <label for="present_address">Present Address</label>
                        <textarea id="present_address" name="present_address" rows="3">{{ old('present_address') }}</textarea>
                    </div>

                    <div class="field full">
                        <label for="previous_school">Previous School</label>
                        <input type="text" id="previous_school" name="previous_school" value="{{ old('previous_school') }}">
                    </div>

                    <div class="field full">
                        <label for="photo">Photo</label>
                        <input type="file" id="photo" name="photo" accept="image/*">
                        <small>Optional — you can also confirm this later.</small>
                    </div>

                    @foreach($documentTypes as $type)
                        <div class="field full">
                            <label for="doc_{{ $type->id }}">{{ $type->name }}{{ $type->is_required ? ' *' : '' }}</label>
                            <input type="file" id="doc_{{ $type->id }}" name="documents[{{ $type->id }}]" accept="image/*,.pdf" {{ $type->is_required ? 'required' : '' }}>
                            <small>{{ $type->is_required ? 'Required' : 'Optional' }} — image or PDF.</small>
                        </div>
                    @endforeach

                    <div class="submit-row">
                        <button type="submit" class="btn btn-primary">Submit Application</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
