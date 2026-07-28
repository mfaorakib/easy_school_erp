@extends('layouts.admin')
@section('title', 'Events')

@section('content')
<div class="page-head"><h1>{{ $event->exists ? __('ui.edit') : __('ui.add') }} — Events</h1>
    <a href="{{ route('communication.events.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $event->exists ? route('communication.events.update', $event) : route('communication.events.store') }}">
        @csrf @if($event->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>Title *</label><input name="title" value="{{ old('title', $event->title) }}" required></div>
            <div><label>Location</label><input name="location" value="{{ old('location', $event->location) }}"></div>
            <div><label>Start Date *</label><input name="start_date" type="date" value="{{ old('start_date', $event->start_date?->format('Y-m-d')) }}" required></div>
            <div><label>End Date</label><input name="end_date" type="date" value="{{ old('end_date', $event->end_date?->format('Y-m-d')) }}"></div>
        </div>
        <div style="margin-top:1rem"><label>Description</label>
            <textarea name="description" rows="4">{{ old('description', $event->description) }}</textarea></div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $event->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
