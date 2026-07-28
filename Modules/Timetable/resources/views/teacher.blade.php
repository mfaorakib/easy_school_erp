@extends('layouts.admin')
@section('title', __('ui.teacher_timetable'))

@section('content')
<div class="page-head"><h1>{{ __('ui.teacher_timetable') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('timetable.teacher') }}">
        <div class="grid">
            <div>
                <label>{{ __('ui.teacher') }}</label>
                <select name="teacher_id">
                    <option value="">—</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ $teacherId == $t->id ? 'selected' : '' }}>{{ $t->displayName() }}</option>
                    @endforeach
                </select>
            </div>
            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">View</button>
            </div>
        </div>
    </form>
</div>

@if(is_null($grid))
    <div class="card"><div class="empty">Select a teacher.</div></div>
@else
<div class="card">
    <div style="overflow-x:auto">
        <table>
            <thead>
                <tr>
                    <th>{{ __('ui.periods') }}</th>
                    @foreach($grid['days'] as $day)
                        <th>{{ ucfirst($day) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @foreach($grid['periods'] as $period)
                <tr>
                    <td>
                        <strong>{{ $period->name }}</strong><br>
                        <small>{{ $period->timeLabel() }}</small>
                    </td>
                    @if($period->is_break)
                        <td colspan="{{ count($grid['days']) }}">{{ __('ui.break') }}</td>
                    @else
                        @foreach($grid['days'] as $day)
                            @php($entry = $grid['entries']->get($period->id.'|'.$day))
                            <td>
                                @if(is_null($entry))
                                    <span class="badge">—</span>
                                @else
                                    <strong>{{ optional($entry->subject)->name }}</strong><br>
                                    <small>
                                        {{ optional($entry->schoolClass)->name }}/{{ optional($entry->section)->name }}@if(optional($entry->classroom)->room_no) · {{ optional($entry->classroom)->room_no }}@endif
                                    </small>
                                @endif
                            </td>
                        @endforeach
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
