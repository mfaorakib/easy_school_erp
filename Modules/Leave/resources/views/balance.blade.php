@extends('layouts.admin')
@section('title', __('ui.leave_balance'))

@section('content')
<div class="page-head"><h1>{{ __('ui.leave_balance') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('leave.balance') }}">
        <div class="grid">
            <div>
                <label>Staff</label>
                <select name="staff_id" onchange="this.form.submit()">
                    <option value="">—</option>
                    @foreach($staff as $s)
                        <option value="{{ $s->id }}" {{ $staffId == $s->id ? 'selected' : '' }}>
                            {{ $s->displayName() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">{{ __('ui.view_all') }}</button>
            </div>
        </div>
    </form>
</div>

@if(is_null($sheet))
    <div class="card"><div class="empty">Select a staff member.</div></div>
@else
<div class="card">
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>{{ __('ui.leave_types') }}</th>
                <th>{{ __('ui.days_allowed') }}</th>
                <th>{{ __('ui.used') }}</th>
                <th>{{ __('ui.remaining') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($sheet as $row)
            <tr>
                <td><strong>{{ $row['type']->name }}</strong></td>
                <td>{{ $row['allowed'] }}</td>
                <td>{{ $row['used'] }}</td>
                <td><span class="badge">{{ $row['remaining'] }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
@endif
@endsection
