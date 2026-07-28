@extends('layouts.admin')
@section('title', 'Vehicles')

@section('content')
<div class="page-head"><h1>Vehicles</h1>
    <a href="{{ route('transport.vehicles.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($vehicles->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Vehicle No</th><th>Model</th><th>Driver</th><th>Capacity</th><th></th></tr></thead>
        <tbody>
        @foreach($vehicles as $v)
            <tr>
                <td><strong>{{ $v->vehicle_no }}</strong></td>
                <td>{{ $v->model ?? '—' }}</td>
                <td>{{ $v->driver_name ?? '—' }}@if($v->driver_phone) <span class="badge">{{ $v->driver_phone }}</span>@endif</td>
                <td>{{ $v->capacity ?? '—' }}</td>
                <td class="actions">
                    <a href="{{ route('transport.vehicles.edit', $v) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('transport.vehicles.destroy', $v) }}" onsubmit="return confirm('Delete?')">
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
