@extends('layouts.admin')
@section('title', 'Suppliers')

@section('content')
<div class="page-head"><h1>{{ $supplier->exists ? __('ui.edit') : __('ui.add') }} — Suppliers</h1>
    <a href="{{ route('inventory.suppliers.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:480px">
    <form method="POST" action="{{ $supplier->exists ? route('inventory.suppliers.update', $supplier) : route('inventory.suppliers.store') }}">
        @csrf @if($supplier->exists) @method('PUT') @endif
        <label>{{ __('ui.name') }} *</label>
        <input name="name" value="{{ old('name', $supplier->name) }}" required>
        <label>Phone</label>
        <input name="phone" value="{{ old('phone', $supplier->phone) }}">
        <label>Email</label>
        <input name="email" type="email" value="{{ old('email', $supplier->email) }}">
        <label>Contact Person</label>
        <input name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}">
        <label>Address</label>
        <textarea name="address">{{ old('address', $supplier->address) }}</textarea>
        <div style="margin-top:1.25rem"><button class="btn">{{ $supplier->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
