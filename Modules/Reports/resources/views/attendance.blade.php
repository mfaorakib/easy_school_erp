@extends('layouts.admin')
@section('title', __('ui.attendance_report'))

@section('content')
<div class="page-head"><h1>{{ __('ui.attendance_report') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('reports.attendance') }}">
        <div class="grid">
            <div>
                <label>{{ __('ui.classes') }}</label>
                <select name="class_id">
                    <option value="">—</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>{{ __('ui.sections') }}</label>
                <select name="section_id">
                    <option value="">—</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}" {{ $sectionId == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>{{ __('ui.from_date') }}</label>
                <input type="date" name="from" value="{{ $from }}">
            </div>
            <div>
                <label>{{ __('ui.to_date') }}</label>
                <input type="date" name="to" value="{{ $to }}">
            </div>
            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">{{ __('ui.generate') }}</button>
            </div>
        </div>
    </form>
</div>

@if(is_null($summary))
<div class="card">
    <div class="empty">Select a class and section, then Generate.</div>
</div>
@else
<div class="card">
    <div class="page-head"><h1>{{ __('ui.attendance_report') }} <span class="badge">{{ $summary['from'] }} → {{ $summary['to'] }}</span></h1></div>
    @if(empty($summary['rows']))
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>Roll</th>
                <th>{{ __('ui.name') }}</th>
                <th>{{ __('ui.present') }}</th>
                <th>{{ __('ui.late') }}</th>
                <th>{{ __('ui.absent') }}</th>
                <th>{{ __('ui.half_day') }}</th>
                <th>{{ __('ui.holiday') }}</th>
                <th>{{ __('ui.attendance_rate') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($summary['rows'] as $r)
            <tr>
                <td>{{ $r['roll_no'] }}</td>
                <td><strong>{{ $r['student']->full_name }}</strong></td>
                <td>{{ $r['present'] }}</td>
                <td>{{ $r['late'] }}</td>
                <td>{{ $r['absent'] }}</td>
                <td>{{ $r['half'] }}</td>
                <td>{{ $r['holiday'] }}</td>
                <td><span class="badge">{{ $r['rate'] }}%</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endif
@endsection
