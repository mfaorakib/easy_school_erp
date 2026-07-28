@extends('layouts.admin')
@section('title', 'Rooms')

@section('content')
<div class="page-head"><h1>Rooms</h1>
    <a href="{{ route('dormitory.rooms.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($rooms->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Room No</th><th>Dormitory</th><th>Type</th><th>Capacity</th><th>Occupied</th><th>Cost</th><th></th></tr></thead>
        <tbody>
        @foreach($rooms as $room)
            <tr>
                <td><strong>{{ $room->room_no }}</strong></td>
                <td>{{ optional($room->dormitory)->name }}</td>
                <td>{{ optional($room->type)->name ?? '—' }}</td>
                <td>{{ $room->capacity }}</td>
                <td><span class="badge">{{ $room->occupancy }} / {{ $room->capacity }}</span></td>
                <td>{{ number_format($room->cost, 2) }}</td>
                <td class="actions">
                    <a href="{{ route('dormitory.rooms.edit', $room) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('dormitory.rooms.destroy', $room) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')<button class="btn btn-sm btn-danger">{{ __('ui.delete') }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
