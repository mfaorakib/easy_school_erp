@extends('layouts.admin')
@section('title', __('ui.generate_payroll'))

@section('content')
<div class="page-head"><h1>{{ __('ui.generate_payroll') }}</h1>
    <a href="{{ route('payroll.staff.index') }}" class="btn btn-sm btn-ghost">{{ __('ui.back') }}</a></div>

<div class="card">
    <p>{{ $assignedCount }} {{ __('ui.staff') }} have an assigned salary template.</p>

    <form method="POST" action="{{ route('payroll.generate.store') }}">
        @csrf
        <div class="grid">
            <div>
                <label>{{ __('ui.period') }}</label>
                <input type="month" name="period" value="{{ $period }}" required>
                @error('period')<div class="badge">{{ $message }}</div>@enderror
            </div>
            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">{{ __('ui.generate_payroll') }}</button>
            </div>
        </div>
    </form>

    <p>Generates a payslip for each staff member with a template. Already-generated months are skipped.</p>
</div>
@endsection
