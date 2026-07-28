@extends('layouts.admin')
@section('title', __('ui.lesson_progress_overview'))

@section('content')
<div class="page-head"><h1>{{ __('ui.lesson_progress_overview') }}</h1></div>

<div class="card">
    <h3 style="margin-top:0">Filter lessons</h3>
    <form method="GET" action="{{ route('lesson.overview.index') }}">
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
    <h3 style="margin-top:0">Lesson progress</h3>
    @if($lessons->isEmpty())
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
                <th>Topics</th>
                <th>Progress</th>
            </tr>
        </thead>
        <tbody>
        @foreach($lessons as $lesson)
            <tr>
                <td><strong>{{ $lesson->title }}</strong></td>
                <td>{{ optional($lesson->schoolClass)->name ?? '—' }} / {{ optional($lesson->section)->name ?? '—' }}</td>
                <td>{{ optional($lesson->subject)->name ?? '—' }}</td>
                <td>{{ optional($lesson->teacher)->full_name ?? '—' }}</td>
                <td><span class="badge">{{ $lesson->completed_count }} / {{ $lesson->topics_count }}</span></td>
                <td><span class="badge">{{ $lesson->progress() }}%</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
