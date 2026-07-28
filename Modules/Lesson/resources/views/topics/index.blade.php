@extends('layouts.admin')
@section('title', 'Lesson Topics')

@section('content')
<div class="page-head"><h1>Lesson Topics</h1></div>

<div class="card">
    <form method="GET" action="{{ route('lesson.topics.index') }}">
        <div class="grid">
            <div>
                <label>{{ __('ui.lessons') }}</label>
                <select name="lesson_id" onchange="this.form.submit()">
                    <option value="">—</option>
                    @foreach($lessons as $l)
                        <option value="{{ $l->id }}" {{ $lesson && $lesson->id == $l->id ? 'selected' : '' }}>
                            {{ $l->title }} — {{ optional($l->schoolClass)->name }}/{{ optional($l->section)->name }} · {{ optional($l->subject)->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">Load</button>
            </div>
        </div>
    </form>
</div>

@if($lesson)
<div class="card">
    <div class="page-head">
        <h1>{{ $lesson->title }} <span class="badge">{{ $lesson->progress() }}%</span></h1>
    </div>

    <form method="POST" action="{{ route('lesson.topics.store') }}">
        @csrf
        <input type="hidden" name="lesson_id" value="{{ $lesson->id }}">
        <div class="grid">
            <div>
                <label>Topic title</label>
                <input type="text" name="title" maxlength="200" required>
            </div>
            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">Add</button>
            </div>
        </div>
    </form>

    @if($lesson->topics->isEmpty())
        <div class="empty">No topics yet.</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>{{ __('ui.status') }}</th>
                <th>Completed on</th>
                <th>{{ __('ui.actions') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($lesson->topics as $topic)
            <tr>
                <td>{{ $topic->title }}</td>
                <td>
                    @if($topic->is_complete)
                        <span class="badge">Complete</span>
                    @else
                        <span class="badge">Pending</span>
                    @endif
                </td>
                <td>{{ $topic->completed_on ? $topic->completed_on->format('d M Y') : '—' }}</td>
                <td>
                    <form method="POST" action="{{ route('lesson.topics.toggle', $topic) }}" style="display:inline">
                        @csrf
                        <button class="btn btn-sm" type="submit">{{ $topic->is_complete ? 'Undo' : 'Mark done' }}</button>
                    </form>
                    <form method="POST" action="{{ route('lesson.topics.destroy', $topic) }}" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm" type="submit">{{ __('ui.delete') }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endif
@endsection
