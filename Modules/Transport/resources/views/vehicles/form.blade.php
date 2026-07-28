@extends('layouts.admin')
@section('title', 'Vehicles')

@section('content')
<div class="page-head"><h1>{{ $vehicle->exists ? __('ui.edit') : __('ui.add') }} — Vehicles</h1>
    <a href="{{ route('transport.vehicles.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $vehicle->exists ? route('transport.vehicles.update', $vehicle) : route('transport.vehicles.store') }}">
        @csrf @if($vehicle->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>Vehicle No *</label><input name="vehicle_no" value="{{ old('vehicle_no', $vehicle->vehicle_no) }}" required></div>
            <div><label>Model</label><input name="model" value="{{ old('model', $vehicle->model) }}"></div>
            <div><label>Driver name</label><input name="driver_name" value="{{ old('driver_name', $vehicle->driver_name) }}"></div>
            <div><label>Driver phone</label><input name="driver_phone" value="{{ old('driver_phone', $vehicle->driver_phone) }}"></div>
            <div><label>Driver license</label><input name="driver_license" value="{{ old('driver_license', $vehicle->driver_license) }}"></div>
            <div><label>Capacity</label><input name="capacity" type="number" min="1" value="{{ old('capacity', $vehicle->capacity) }}"></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $vehicle->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
