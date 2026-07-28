@extends('layouts.admin')
@section('title', 'Stores')

@section('content')
<div class="page-head"><h1>{{ $store->exists ? __('ui.edit') : __('ui.add') }} — Stores</h1>
    <a href="{{ route('inventory.stores.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:480px">
    <form method="POST" action="{{ $store->exists ? route('inventory.stores.update', $store) : route('inventory.stores.store') }}">
        @csrf @if($store->exists) @method('PUT') @endif
        <label>{{ __('ui.name') }} *</label>
        <input name="name" value="{{ old('name', $store->name) }}" required>
        <label>Code</label>
        <input name="code" value="{{ old('code', $store->code) }}">
        <div style="margin-top:1.25rem"><button class="btn">{{ $store->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
