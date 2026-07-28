@extends('layouts.admin')
@section('title', __('ui.class_routine'))

@section('content')
<div class="page-head"><h1>{{ __('ui.class_routine') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('timetable.routine') }}">
        <div class="grid">
            <div>
                <label>{{ __('ui.classes') }}</label>
                <select name="class_id">
                    <option value="">—</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>{{ __('ui.sections') }}</label>
                <select name="section_id">
                    <option value="">—</option>
                    @foreach($sections as $s)
                        <option value="{{ $s->id }}" {{ $sectionId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">{{ __('ui.generate') }}</button>
            </div>
        </div>
    </form>
</div>

@if(is_null($grid))
    <div class="card"><div class="empty">{{ __('ui.select_class_section') }}</div></div>
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
                                        {{ optional($entry->teacher)->displayName() }}@if(optional($entry->classroom)->room_no) · {{ optional($entry->classroom)->room_no }}@endif
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
