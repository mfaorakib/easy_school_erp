@extends('layouts.admin')
@section('title', __('ui.homework_overview'))

@section('content')
<div class="page-head"><h1>{{ __('ui.homework_overview') }}</h1></div>

<div class="card">
    <h3 style="margin-top:0">Filter homework</h3>
    <form method="GET" action="{{ route('homework.overview.index') }}">
        <div class="grid">
            <div><label>{{ __('ui.classes') }}</label>
                <select name="class_id">
                    <option value="">All classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" @selected($classId === $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>{{ __('ui.sections') }}</label>
                <select name="section_id">
                    <option value="">All sections</option>
                    @foreach($sections as $s)
                        <option value="{{ $s->id }}" @selected($sectionId === $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;align-items:flex-end"><button class="btn">{{ __('ui.load') }}</button></div>
        </div>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0">Homework &amp; evaluation</h3>
    @if($homeworks->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Class / Section</th>
                <th>Subject</th>
                <th>Teacher</th>
                <th>Homework date</th>
                <th>Submission</th>
                <th>Evaluated</th>
            </tr>
        </thead>
        <tbody>
        @foreach($homeworks as $h)
            <tr>
                <td><strong>{{ $h->title }}</strong></td>
                <td>{{ optional($h->schoolClass)->name ?? '—' }} / {{ optional($h->section)->name ?? '—' }}</td>
                <td>{{ optional($h->subject)->name ?? '—' }}</td>
                <td>{{ optional($h->teacher)->full_name ?? '—' }}</td>
                <td>{{ optional($h->homework_date)->format('d M Y') }}</td>
                <td>{{ optional($h->submission_date)->format('d M Y') }}</td>
                <td><span class="badge">{{ $h->completed_count }} / {{ $h->students_count }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
