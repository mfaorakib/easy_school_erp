@extends('layouts.admin')
@section('title', 'New Admission')

@section('content')
<div class="page-head"><h1>New Admission</h1>
    <a href="{{ route('academic.students.index') }}" class="btn btn-ghost">Back</a></div>

<form method="POST" action="{{ route('academic.students.store') }}">
    @csrf

    <div class="card">
        <h3 style="margin-top:0">Student</h3>
        <div class="grid">
            <div><label>First name *</label><input name="first_name" value="{{ old('first_name') }}" required></div>
            <div><label>Last name</label><input name="last_name" value="{{ old('last_name') }}"></div>
            <div><label>Gender</label>
                <select name="gender_id">
                    <option value="">—</option>
                    @foreach($genders as $g)
                        <option value="{{ $g->id }}" {{ old('gender_id') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>Date of birth</label><input name="date_of_birth" type="date" value="{{ old('date_of_birth') }}"></div>
            <div><label>Email</label><input name="email" type="email" value="{{ old('email') }}"></div>
            <div><label>Mobile</label><input name="mobile" value="{{ old('mobile') }}"></div>
            <div><label>Admission #</label><input name="admission_no" type="number" value="{{ old('admission_no', $nextAdmission) }}"></div>
            <div><label>Roll #</label><input name="roll_no" type="number" value="{{ old('roll_no') }}"></div>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-top:0">Enrollment</h3>
        <div class="grid">
            <div><label>Class *</label>
                <select name="class_id" required>
                    <option value="">—</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>Section *</label>
                <select name="section_id" required>
                    <option value="">—</option>
                    @foreach($sections as $s)
                        <option value="{{ $s->id }}" {{ old('section_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>Category</label>
                <select name="student_category_id">
                    <option value="">—</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('student_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>Group</label>
                <select name="student_group_id">
                    <option value="">—</option>
                    @foreach($groups as $grp)
                        <option value="{{ $grp->id }}" {{ old('student_group_id') == $grp->id ? 'selected' : '' }}>{{ $grp->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-top:0">Guardian</h3>
        <p class="l" style="color:var(--muted);font-size:.82rem;margin-top:0">Leave blank to admit without a guardian account.</p>
        <div class="grid">
            <div><label>Guardian name</label><input name="guardian_name" value="{{ old('guardian_name') }}"></div>
            <div><label>Guardian mobile</label><input name="guardian_mobile" value="{{ old('guardian_mobile') }}"></div>
            <div><label>Guardian email</label><input name="guardian_email" type="email" value="{{ old('guardian_email') }}"></div>
            <div><label>Relation</label><input name="guardian_relation" value="{{ old('guardian_relation') }}"></div>
        </div>
    </div>

    <button class="btn">Admit Student</button>
</form>
@endsection
