@extends('layouts.admin')
@section('title', __('ui.guardian_report'))

@section('content')
<div class="page-head"><h1>{{ __('ui.guardian_report') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('reports.guardians') }}">
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
    <div class="page-head"><h1>{{ __('ui.guardian_report') }} <span class="badge">{{ $rows->count() }}</span></h1></div>
    @if($rows->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>{{ __('ui.name') }}</th>
                <th>{{ __('ui.guardian') }}</th>
                <th>Relation</th>
                <th>{{ __('ui.mobile') }}</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
        @foreach($rows as $row)
            <tr>
                <td><strong>{{ $row['student']->full_name }}</strong></td>
                <td>{{ optional($row['guardian'])->guardian_name ?: optional($row['guardian'])->father_name ?: '—' }}</td>
                <td>{{ optional($row['guardian'])->guardian_relation ?: '—' }}</td>
                <td>{{ optional($row['guardian'])->guardian_mobile ?: optional($row['guardian'])->father_mobile ?: '—' }}</td>
                <td>{{ optional($row['guardian'])->guardian_email ?: '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
