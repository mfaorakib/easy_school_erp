@extends('layouts.admin')
@section('title', __('ui.fee_discounts'))

@section('content')
<div class="page-head"><h1>{{ $discount->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.fee_discounts') }}</h1>
    <a href="{{ route('fees.discounts.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:520px">
    <form method="POST" action="{{ $discount->exists ? route('fees.discounts.update', $discount) : route('fees.discounts.store') }}">
        @csrf @if($discount->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.name') }} *</label><input name="name" value="{{ old('name', $discount->name) }}" required></div>
            <div><label>Code</label><input name="code" value="{{ old('code', $discount->code) }}"></div>
            <div><label>Type *</label>
                <select name="amount_type">
                    <option value="fixed" {{ old('amount_type', $discount->amount_type) === 'fixed' ? 'selected' : '' }}>Fixed</option>
                    <option value="percentage" {{ old('amount_type', $discount->amount_type) === 'percentage' ? 'selected' : '' }}>Percentage</option>
                </select>
            </div>
            <div><label>{{ __('ui.amount') }} *</label><input name="amount" type="number" step="any" value="{{ old('amount', $discount->amount) }}" required></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $discount->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
