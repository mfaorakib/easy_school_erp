@extends('layouts.admin')
@section('title', __('ui.timetable_builder'))

@section('content')
<div class="page-head"><h1>{{ __('ui.timetable_builder') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('timetable.builder') }}">
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

@if($grid === null)
    <div class="card"><div class="empty">{{ __('ui.select_class_section') }}</div></div>
@else
    @if(!empty($clashes))
    <div class="card">
        <p class="badge">{{ __('ui.clash_warning') }}</p>
        <ul>
            @foreach($clashes as $c)
                <li>{{ optional($c['entry']->subject)->name }} ({{ optional($c['entry']->period)->name }}) — {{ optional($c['conflict']->schoolClass)->name }}/{{ optional($c['conflict']->section)->name }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('timetable.builder.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="class_id" value="{{ $classId }}">
            <input type="hidden" name="section_id" value="{{ $sectionId }}">

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
                                {{ $period->name }}<br>
                                <small>{{ $period->timeLabel() }}</small>
                            </td>
                            @if($period->is_break)
                                <td colspan="{{ count($grid['days']) }}">{{ __('ui.break') }}</td>
                            @else
                                @foreach($grid['days'] as $day)
                                    @php($entry = $grid['entries']->get($period->id.'|'.$day))
                                    <td>
                                        <select name="entries[{{ $period->id }}][{{ $day }}][subject_id]" style="font-size:smaller">
                                            <option value="">—</option>
                                            @foreach($subjects as $s)
                                                <option value="{{ $s->id }}" {{ optional($entry)->subject_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                            @endforeach
                                        </select>
                                        <select name="entries[{{ $period->id }}][{{ $day }}][teacher_id]" style="font-size:smaller">
                                            <option value="">—</option>
                                            @foreach($teachers as $t)
                                                <option value="{{ $t->id }}" {{ optional($entry)->teacher_id == $t->id ? 'selected' : '' }}>{{ $t->displayName() }}</option>
                                            @endforeach
                                        </select>
                                        <select name="entries[{{ $period->id }}][{{ $day }}][classroom_id]" style="font-size:smaller">
                                            <option value="">—</option>
                                            @foreach($rooms as $r)
                                                <option value="{{ $r->id }}" {{ optional($entry)->classroom_id == $r->id ? 'selected' : '' }}>{{ $r->room_no }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                @endforeach
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <button class="btn btn-sm" type="submit">{{ __('ui.save_timetable') }}</button>
        </form>
    </div>
@endif
@endsection
