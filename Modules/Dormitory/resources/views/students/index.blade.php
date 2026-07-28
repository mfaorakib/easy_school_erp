@extends('layouts.admin')
@section('title', 'Allocate students to dormitory')

@section('content')
<div class="page-head"><h1>Allocate students to dormitory</h1></div>

<div class="card">
    <h3 style="margin-top:0">Allocate student</h3>
    @if($students->isEmpty())
        <div class="empty">No active students available yet.</div>
    @elseif($rooms->isEmpty())
        <div class="empty">No active rooms available yet. Add a dormitory room first.</div>
    @else
    <form method="POST" action="{{ route('dormitory.students.store') }}">
        @csrf
        <div class="grid">
            <div><label>Student *</label>
                <select name="student_id" required>
                    <option value="">—</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}">{{ $s->full_name }} ({{ $s->admission_no }})</option>
                    @endforeach
                </select>
            </div>
            <div><label>Room *</label>
                <select name="dormitory_room_id" required>
                    <option value="">—</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}">{{ $room->dormitory?->name }} · Room {{ $room->room_no }} ({{ $room->free_beds }} free)</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div style="margin-top:1rem"><button class="btn">{{ __('ui.assign') }}</button></div>
    </form>
    @endif
</div>

<div class="card">
    <h3 style="margin-top:0">Current allocations</h3>
    @if($allocations->isEmpty())
        <div class="empty">No students allocated to a dormitory yet.</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Student</th><th>Dormitory</th><th>Room</th><th></th></tr></thead>
        <tbody>
        @foreach($allocations as $a)
            <tr>
                <td>{{ $a->student?->full_name }}</td>
                <td>{{ $a->room?->dormitory?->name }}</td>
                <td><span class="badge">{{ $a->room?->room_no }}</span></td>
                <td>
                    <form method="POST" action="{{ route('dormitory.students.unassign') }}" onsubmit="return confirm('Remove this allocation?')">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $a->student_id }}">
                        <button class="btn btn-sm">Unassign</button>
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
