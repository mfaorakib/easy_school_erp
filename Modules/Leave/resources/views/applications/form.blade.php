@extends('layouts.admin')
@section('title', 'Apply Leave')

@section('content')
<div class="page-head"><h1>{{ __('ui.apply_leave') }}</h1>
    <a href="{{ route('leave.applications.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>

<div class="card" style="max-width:680px">
    <form method="POST" action="{{ route('leave.applications.store') }}">
        @csrf
        <div class="grid">
            <div><label>Staff *</label>
                <select name="staff_id" required>
                    <option value="">—</option>
                    @foreach($staff as $s)<option value="{{ $s->id }}" {{ old('staff_id') == $s->id ? 'selected' : '' }}>{{ $s->displayName() }}</option>@endforeach
                </select>
                @error('staff_id')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div><label>{{ __('ui.leave_types') }} *</label>
                <select name="leave_type_id" required>
                    <option value="">—</option>
                    @foreach($types as $t)<option value="{{ $t->id }}" {{ old('leave_type_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach
                </select>
                @error('leave_type_id')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div><label>From *</label>
                <input name="from_date" type="date" value="{{ old('from_date', date('Y-m-d')) }}" required>
                @error('from_date')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div><label>To *</label>
                <input name="to_date" type="date" value="{{ old('to_date') }}" required>
                @error('to_date')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div style="grid-column:1/-1"><label>{{ __('ui.reason') }}</label>
                <textarea name="reason" rows="4" maxlength="500">{{ old('reason') }}</textarea>
                @error('reason')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ __('ui.apply_leave') }}</button></div>
    </form>
</div>
@endsection
