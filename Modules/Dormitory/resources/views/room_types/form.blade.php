@extends('layouts.admin')
@section('title', 'Room Types')

@section('content')
<div class="page-head"><h1>{{ $roomType->exists ? __('ui.edit') : __('ui.add') }} — Room Types</h1>
    <a href="{{ route('dormitory.room-types.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $roomType->exists ? route('dormitory.room-types.update', $roomType) : route('dormitory.room-types.store') }}">
        @csrf @if($roomType->exists) @method('PUT') @endif
        <div class="grid">
            <div style="grid-column:1/-1"><label>{{ __('ui.name') }} *</label><input name="name" value="{{ old('name', $roomType->name) }}" required></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $roomType->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
