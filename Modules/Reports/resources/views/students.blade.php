@extends('layouts.admin')
@section('title', __('ui.student_report'))

@section('content')
<div class="page-head"><h1>{{ __('ui.student_report') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('reports.students') }}">
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
            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">{{ __('ui.generate') }}</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="page-head"><h1>{{ __('ui.student_report') }} <span class="badge">{{ $rows->count() }}</span></h1></div>
    @if($rows->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>Roll</th>
                <th>{{ __('ui.name') }}</th>
                <th>Admission No</th>
                <th>Class</th>
                <th>Section</th>
                <th>{{ __('ui.mobile') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{{ $row['record']->roll_no }}</td>
                <td><strong>{{ $row['student']->full_name }}</strong></td>
                <td>{{ $row['student']->admission_no }}</td>
                <td>{{ optional($row['record']->schoolClass)->name }}</td>
                <td>{{ optional($row['record']->section)->name }}</td>
                <td>{{ $row['student']->mobile ?: '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
