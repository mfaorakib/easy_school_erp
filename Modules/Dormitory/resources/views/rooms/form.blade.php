@extends('layouts.admin')
@section('title', 'Rooms')

@section('content')
<div class="page-head"><h1>{{ $room->exists ? __('ui.edit') : __('ui.add') }} — Room</h1>
    <a href="{{ route('dormitory.rooms.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $room->exists ? route('dormitory.rooms.update', $room) : route('dormitory.rooms.store') }}">
        @csrf @if($room->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>Dormitory *</label>
                <select name="dormitory_id" required>
                    <option value="">—</option>
                    @foreach($dormitories as $d)<option value="{{ $d->id }}" {{ old('dormitory_id', $room->dormitory_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach
                </select>
            </div>
            <div><label>Type (optional)</label>
                <select name="room_type_id">
                    <option value="">—</option>
                    @foreach($roomTypes as $t)<option value="{{ $t->id }}" {{ old('room_type_id', $room->room_type_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach
                </select>
            </div>
            <div><label>Room No *</label><input name="room_no" type="text" maxlength="50" value="{{ old('room_no', $room->room_no) }}" required></div>
            <div><label>Capacity *</label><input name="capacity" type="number" min="1" value="{{ old('capacity', $room->capacity) }}" required></div>
            <div><label>Cost</label><input name="cost" type="number" step="any" min="0" value="{{ old('cost', $room->cost) }}"></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $room->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
