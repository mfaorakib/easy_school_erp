@extends('layouts.admin')
@section('title', 'Assign Shifts')

@section('content')
<div class="page-head"><h1>{{ __('ui.assign_shifts') }}</h1>
    <a href="{{ route('leave.shifts.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>

<div class="card">
    @if($staff->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>Current Shift</th><th>{{ __('ui.assign_shifts') }}</th></tr></thead>
        <tbody>
        @foreach($staff as $s)
            <tr>
                <td><strong>{{ $s->displayName() }}</strong></td>
                <td><span class="badge">{{ optional(optional($assigned->get($s->id))->shift)->name ?: '—' }}</span></td>
                <td>
                    @if($shifts->isEmpty())
                        <span class="empty">Create a shift first.</span>
                    @else
                    <form method="POST" action="{{ route('leave.shifts.assign.store') }}" class="actions">
                        @csrf
                        <input type="hidden" name="staff_id" value="{{ $s->id }}">
                        <select name="shift_id">
                            @foreach($shifts as $sh)
                                <option value="{{ $sh->id }}" {{ optional($assigned->get($s->id))->shift_id == $sh->id ? 'selected' : '' }}>{{ $sh->name }} — {{ $sh->timeLabel() }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm">{{ __('ui.assign_shifts') }}</button>
                    </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
