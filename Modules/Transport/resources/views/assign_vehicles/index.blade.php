@extends('layouts.admin')
@section('title', 'Assign vehicles')

@section('content')
<div class="page-head"><h1>Assign vehicles to routes</h1></div>

<div class="card">
    <h3 style="margin-top:0">Assign vehicles</h3>
    @if($routes->isEmpty())
        <div class="empty">No routes available yet. Add a transport route first.</div>
    @elseif($vehicles->isEmpty())
        <div class="empty">No active vehicles available yet. Add a vehicle first.</div>
    @else
    <form method="POST" action="{{ route('transport.assign-vehicles.store') }}">
        @csrf
        <div class="grid">
            <div><label>Route</label>
                <select name="transport_route_id" required>
                    <option value="">—</option>
                    @foreach($routes as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach
                </select>
            </div>
            <div><label>Vehicle(s)</label>
                <select name="vehicles[]" multiple size="6">
                    @foreach($vehicles as $v)<option value="{{ $v->id }}">{{ $v->vehicle_no }}@if($v->driver_name) — {{ $v->driver_name }}@endif</option>@endforeach
                </select>
            </div>
        </div>
        <div style="margin-top:1rem"><button class="btn">{{ __('ui.assign') }}</button></div>
    </form>
    @endif
</div>

<div class="card">
    <h3 style="margin-top:0">Assigned vehicles</h3>
    @if($routes->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Route</th><th>Fare</th><th>Vehicle(s)</th></tr></thead>
        <tbody>
        @foreach($routes as $r)
            <tr>
                <td>{{ $r->name }}</td>
                <td>{{ $r->fare }}</td>
                <td>
                    @forelse($r->vehicles as $v)
                        <span class="badge">{{ $v->vehicle_no }}@if($v->driver_name) — {{ $v->driver_name }}@endif</span>
                    @empty
                        <span class="empty">None</span>
                    @endforelse
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
