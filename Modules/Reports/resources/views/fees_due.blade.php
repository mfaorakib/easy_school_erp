@extends('layouts.admin')
@section('title', __('ui.fees_due_report'))

@section('content')
<div class="page-head"><h1>{{ __('ui.fees_due_report') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('reports.fees.due') }}">
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
    <div class="page-head">
        <div>
            <span class="badge">{{ __('ui.total_due') }}: {{ number_format($data['total_due'], 2) }}</span>
        </div>
    </div>

    @if(empty($data['rows']))
        <div class="empty">No dues.</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>Roll</th>
                <th>Student</th>
                <th>Admission No</th>
                <th>Net</th>
                <th>Paid/Settled</th>
                <th>Due</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data['rows'] as $r)
            <tr>
                <td>{{ $r['roll_no'] }}</td>
                <td>{{ $r['student']->full_name }}</td>
                <td>{{ $r['student']->admission_no }}</td>
                <td>{{ number_format($r['net'], 2) }}</td>
                <td>{{ number_format($r['settled'], 2) }}</td>
                <td><span class="badge">{{ number_format($r['due'], 2) }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
