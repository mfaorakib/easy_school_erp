@extends('layouts.admin')
@section('title', 'Routes')

@section('content')
<div class="page-head"><h1>{{ $route->exists ? __('ui.edit') : __('ui.add') }} — Routes</h1>
    <a href="{{ route('transport.routes.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $route->exists ? route('transport.routes.update', $route) : route('transport.routes.store') }}">
        @csrf @if($route->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.name') }} *</label><input name="name" value="{{ old('name', $route->name) }}" required></div>
            <div><label>Fare *</label><input name="fare" type="number" step="any" min="0" value="{{ old('fare', $route->fare) }}" required></div>
            <div><label>Start point</label><input name="start_point" value="{{ old('start_point', $route->start_point) }}"></div>
            <div><label>End point</label><input name="end_point" value="{{ old('end_point', $route->end_point) }}"></div>
            <div style="grid-column:1/-1"><label>Note</label><textarea name="note" rows="3">{{ old('note', $route->note) }}</textarea></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $route->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
