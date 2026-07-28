@extends('layouts.admin')
@section('title', __('ui.fee_masters'))

@section('content')
<div class="page-head"><h1>{{ $master->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.fee_masters') }}</h1>
    <a href="{{ route('fees.masters.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $master->exists ? route('fees.masters.update', $master) : route('fees.masters.store') }}">
        @csrf @if($master->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>Group *</label>
                <select name="fee_group_id" required>
                    <option value="">—</option>
                    @foreach($groups as $g)<option value="{{ $g->id }}" {{ old('fee_group_id', $master->fee_group_id) == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>@endforeach
                </select>
            </div>
            <div><label>Type *</label>
                <select name="fee_type_id" required>
                    <option value="">—</option>
                    @foreach($types as $t)<option value="{{ $t->id }}" {{ old('fee_type_id', $master->fee_type_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach
                </select>
            </div>
            <div><label>{{ __('ui.classes') }} (optional)</label>
                <select name="class_id">
                    <option value="">Any class</option>
                    @foreach($classes as $c)<option value="{{ $c->id }}" {{ old('class_id', $master->class_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div><label>{{ __('ui.amount') }} *</label><input name="amount" type="number" step="any" value="{{ old('amount', $master->amount) }}" required></div>
            <div><label>Due date</label><input name="due_date" type="date" value="{{ old('due_date', optional($master->due_date)->format('Y-m-d')) }}"></div>
            <div><label>Fine type</label>
                <select name="fine_type">
                    @foreach(['none' => 'None', 'percentage' => 'Percentage', 'fixed' => 'Fixed'] as $v => $l)
                        <option value="{{ $v }}" {{ old('fine_type', $master->fine_type ?? 'none') === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>Fine value</label><input name="fine_value" type="number" step="any" value="{{ old('fine_value', $master->fine_value ?? 0) }}"></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $master->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
