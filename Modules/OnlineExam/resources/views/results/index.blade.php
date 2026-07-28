@extends('layouts.admin')
@section('title', 'Exam Results')

@section('content')
<div class="page-head"><h1>{{ __('ui.online_exam') }} — {{ __('ui.results') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('onlineexam.results.index') }}">
        <div class="grid">
            <div>
                <label>{{ __('ui.exams') }}</label>
                <select name="exam_id" onchange="this.form.submit()">
                    <option value="">—</option>
                    @foreach($exams as $e)
                        <option value="{{ $e->id }}" {{ $selectedExam && $selectedExam->id == $e->id ? 'selected' : '' }}>
                            {{ $e->title }} — {{ optional($e->schoolClass)->name }}/{{ optional($e->section)->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">{{ __('ui.load') }}</button>
            </div>
        </div>
    </form>
</div>

@if($selectedExam)
<div class="card">
    <div class="page-head"><h1>{{ $selectedExam->title }} <span class="badge">{{ __('ui.total_marks') }}: {{ $selectedExam->totalMarks() }}</span></h1></div>

    @if($attempts && $attempts->isNotEmpty())
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>{{ __('ui.students') }}</th>
            <th>{{ __('ui.status') }}</th>
            <th>{{ __('ui.total_marks') }}</th>
            <th>{{ __('ui.actions') }}</th>
        </tr></thead>
        <tbody>
        @foreach($attempts as $attempt)
            <tr>
                <td>{{ optional($attempt->student)->full_name }}</td>
                <td>
                    @if($attempt->awaitsMarking())
                        <span class="badge">{{ __('ui.awaiting_marking') }}</span>
                    @elseif($attempt->status === \Modules\OnlineExam\Models\OnlineExamAttempt::STATUS_MARKED)
                        <span class="badge">Marked</span>
                    @elseif($attempt->status === \Modules\OnlineExam\Models\OnlineExamAttempt::STATUS_SUBMITTED)
                        <span class="badge">Submitted</span>
                    @else
                        <span class="badge">{{ __('ui.pending') }}</span>
                    @endif
                </td>
                <td>{{ $attempt->total_marks }} / {{ $selectedExam->totalMarks() }}</td>
                <td class="actions">
                    <a href="{{ route('onlineexam.results.show', $attempt) }}" class="btn btn-sm btn-ghost">View</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @else
        <div class="empty">{{ __('ui.no_records') }}</div>
    @endif
</div>
@endif
@endsection
