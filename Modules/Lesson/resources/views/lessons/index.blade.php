@extends('layouts.admin')
@section('title', 'Lessons')

@section('content')
<div class="page-head"><h1>Lessons</h1>
    <a href="{{ route('lesson.lessons.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($lessons->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>{{ __('ui.classes') }}</th><th>{{ __('ui.sections') }}</th><th>{{ __('ui.subjects') }}</th><th>Date</th><th>Progress</th><th></th></tr></thead>
        <tbody>
        @foreach($lessons as $lesson)
            <tr>
                <td><strong>{{ $lesson->title }}</strong></td>
                <td>{{ optional($lesson->schoolClass)->name }}</td>
                <td>{{ optional($lesson->section)->name }}</td>
                <td>{{ optional($lesson->subject)->name }}</td>
                <td>{{ $lesson->lesson_date ? $lesson->lesson_date->format('d M Y') : '—' }}</td>
                <td><span class="badge">{{ $lesson->progress() }}%</span></td>
                <td class="actions">
                    <a href="{{ route('lesson.lessons.edit', $lesson) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('lesson.lessons.destroy', $lesson) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')<button class="btn btn-sm btn-danger">{{ __('ui.delete') }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
