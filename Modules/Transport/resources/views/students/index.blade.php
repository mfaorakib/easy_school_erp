@extends('layouts.admin')
@section('title', 'Assign students to transport')

@section('content')
<div class="page-head"><h1>Assign students to transport</h1></div>

<div class="card">
    <h3 style="margin-top:0">Assign student</h3>
    @if($students->isEmpty())
        <div class="empty">No active students available yet.</div>
    @elseif($routes->isEmpty())
        <div class="empty">No active routes available yet. Add a transport route first.</div>
    @else
    <form method="POST" action="{{ route('transport.students.store') }}">
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
            <div><label>Route *</label>
                <select name="transport_route_id" required>
                    <option value="">—</option>
                    @foreach($routes as $r)
                        <option value="{{ $r->id }}">{{ $r->name }} — {{ $r->fare }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>Vehicle</label>
                <select name="vehicle_id">
                    <option value="">—</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->vehicle_no }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div style="margin-top:1rem"><button class="btn">{{ __('ui.assign') }}</button></div>
    </form>
    @endif
</div>

<div class="card">
    <h3 style="margin-top:0">Current assignments</h3>
    @if($assignments->isEmpty())
        <div class="empty">No students assigned to transport yet.</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Student</th><th>Route</th><th>Vehicle</th><th>Fare</th><th></th></tr></thead>
        <tbody>
        @foreach($assignments as $a)
            <tr>
                <td>{{ $a->student?->full_name }}</td>
                <td>{{ $a->route?->name }}</td>
                <td>
                    @if($a->vehicle)
                        <span class="badge">{{ $a->vehicle->vehicle_no }}</span>
                    @else
                        <span class="empty">None</span>
                    @endif
                </td>
                <td>{{ $a->route?->fare }}</td>
                <td>
                    <form method="POST" action="{{ route('transport.students.unassign') }}" onsubmit="return confirm('Remove this assignment?')">
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
