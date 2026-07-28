@extends('layouts.admin')
@section('title', 'Visitors')

@section('content')
<div class="page-head"><h1>{{ $visitor->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.visitors') }}</h1>
    <a href="{{ route('frontoffice.visitors.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $visitor->exists ? route('frontoffice.visitors.update', $visitor) : route('frontoffice.visitors.store') }}">
        @csrf @if($visitor->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.name') }} *</label><input name="name" type="text" maxlength="255" value="{{ old('name', $visitor->name) }}" required></div>
            <div><label>{{ __('ui.mobile') }}</label><input name="phone" type="text" maxlength="40" value="{{ old('phone', $visitor->phone) }}"></div>
            <div><label>{{ __('ui.purpose') }}</label><input name="purpose" type="text" maxlength="255" value="{{ old('purpose', $visitor->purpose) }}"></div>
            <div><label>{{ __('ui.to_meet') }}</label><input name="to_meet" type="text" maxlength="255" value="{{ old('to_meet', $visitor->to_meet) }}"></div>
            <div><label>ID Card</label><input name="id_card" type="text" maxlength="100" value="{{ old('id_card', $visitor->id_card) }}"></div>
            <div><label>No. of Persons</label><input name="no_of_person" type="number" min="1" value="{{ old('no_of_person', $visitor->no_of_person ?? 1) }}"></div>
            <div><label>{{ __('ui.visit_date') }} *</label><input name="visit_date" type="date" value="{{ old('visit_date', $visitor->exists ? optional($visitor->visit_date)->format('Y-m-d') : now()->format('Y-m-d')) }}" required></div>
            <div><label>In Time</label><input name="in_time" type="time" value="{{ old('in_time', $visitor->in_time ? substr($visitor->in_time, 0, 5) : '') }}"></div>
            <div><label>Out Time</label><input name="out_time" type="time" value="{{ old('out_time', $visitor->out_time ? substr($visitor->out_time, 0, 5) : '') }}"></div>
            <div style="grid-column:1/-1"><label>Note</label><textarea name="note" rows="3" maxlength="255">{{ old('note', $visitor->note) }}</textarea></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $visitor->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
