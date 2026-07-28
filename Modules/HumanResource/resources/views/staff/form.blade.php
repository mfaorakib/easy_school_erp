@extends('layouts.admin')
@section('title', $staff->exists ? __('ui.edit').' '.__('ui.staff') : __('ui.add').' '.__('ui.staff'))

@section('content')
<div class="page-head">
    <h1>{{ $staff->exists ? __('ui.edit') : __('ui.add') }} {{ __('ui.staff') }}</h1>
    <a href="{{ route('hr.staff.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a>
</div>

<form method="POST" action="{{ $staff->exists ? route('hr.staff.update', $staff) : route('hr.staff.store') }}">
    @csrf
    @if($staff->exists) @method('PUT') @endif

    <div class="card">
        <h3 style="margin-top:0">Profile</h3>
        <div class="grid">
            <div><label>First name *</label><input name="first_name" value="{{ old('first_name', $staff->first_name) }}" required></div>
            <div><label>Last name</label><input name="last_name" value="{{ old('last_name', $staff->last_name) }}"></div>
            <div><label>Role *</label>
                <select name="role" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->value }}" {{ old('role', $currentRole) === $role->value ? 'selected' : '' }}>{{ $role->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>Gender</label>
                <select name="gender_id">
                    <option value="">—</option>
                    @foreach($genders as $g)
                        <option value="{{ $g->id }}" {{ old('gender_id', $staff->gender_id) == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>Designation</label>
                <select name="designation_id">
                    <option value="">—</option>
                    @foreach($designations as $d)
                        <option value="{{ $d->id }}" {{ old('designation_id', $staff->designation_id) == $d->id ? 'selected' : '' }}>{{ $d->title }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>Department</label>
                <select name="department_id">
                    <option value="">—</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ old('department_id', $staff->department_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-top:0">Contact &amp; Employment</h3>
        <div class="grid">
            <div><label>Mobile</label><input name="mobile" value="{{ old('mobile', $staff->mobile) }}"></div>
            <div><label>Email</label><input name="email" type="email" value="{{ old('email', $staff->email) }}"></div>
            <div><label>Date of birth</label><input name="date_of_birth" type="date" value="{{ old('date_of_birth', optional($staff->date_of_birth)->format('Y-m-d')) }}"></div>
            <div><label>Date of joining</label><input name="date_of_joining" type="date" value="{{ old('date_of_joining', optional($staff->date_of_joining)->format('Y-m-d')) }}"></div>
            <div>
                <label>Leaving Date</label>
                <input name="leaving_date" type="date" value="{{ old('leaving_date', optional($staff->leaving_date)->format('Y-m-d')) }}">
                <small style="color:var(--muted)">Leave blank if still employed — used to auto-fill Experience Certificates.</small>
            </div>
            <div><label>Qualification</label><input name="qualification" value="{{ old('qualification', $staff->qualification) }}"></div>
            <div><label>Basic salary</label><input name="basic_salary" type="number" step="any" value="{{ old('basic_salary', $staff->basic_salary) }}"></div>
            <div style="align-self:end"><label style="font-weight:normal"><input name="is_active" type="checkbox" value="1" {{ old('is_active', $staff->exists ? $staff->is_active : true) ? 'checked' : '' }}> Active</label></div>
        </div>
        <label>Address</label>
        <textarea name="current_address" rows="2">{{ old('current_address', $staff->current_address) }}</textarea>
    </div>

    <div class="card">
        <h3 style="margin-top:0">Bank Details</h3>
        <div class="grid">
            <div><label>{{ __('ui.bank_name') }}</label><input name="bank_name" value="{{ old('bank_name', $staff->bank_name) }}"></div>
            <div><label>{{ __('ui.bank_account_no') }}</label><input name="bank_account_no" value="{{ old('bank_account_no', $staff->bank_account_no) }}"></div>
            <div><label>{{ __('ui.bank_branch') }}</label><input name="bank_branch" value="{{ old('bank_branch', $staff->bank_branch) }}"></div>
        </div>
    </div>

    @if(! $staff->exists)
        <p class="empty" style="text-align:left;color:var(--muted);padding:0 0 1rem">Default password: <strong>password</strong> (login by email or mobile).</p>
    @endif

    <button class="btn">{{ $staff->exists ? __('ui.update') : __('ui.save') }}</button>
</form>
@endsection
