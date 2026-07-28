@extends('layouts.admin')
@section('title', __('ui.my_exams'))

@section('content')
<div class="page-head"><h1>{{ __('ui.my_exams') }}</h1></div>

@if(! $student)
    <div class="card">
        <div class="empty">This page is for student accounts.</div>
    </div>
@elseif($exams->isEmpty())
    <div class="card">
        <div class="empty">{{ __('ui.no_records') }}</div>
    </div>
@else
<div class="card">
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>{{ __('ui.name') }}</th>
                <th>{{ __('ui.subjects') }}</th>
                <th>Date</th>
                <th>{{ __('ui.marks') }}</th>
                <th>{{ __('ui.status') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($exams as $exam)
            @php($attempt = $attempts[$exam->id] ?? null)
            <tr>
                <td><strong>{{ $exam->title }}</strong></td>
                <td>{{ optional($exam->subject)->name }}</td>
                <td>{{ $exam->exam_date ? \Illuminate\Support\Carbon::parse($exam->exam_date)->format('d M Y') : '—' }}</td>
                <td>{{ $exam->totalMarks() }}</td>
                <td class="actions">
                    @if(! $attempt)
                        <a href="{{ route('onlineexam.student.take', $exam) }}" class="btn btn-sm">{{ __('ui.take_exam') }}</a>
                    @elseif($attempt->status === \Modules\OnlineExam\Models\OnlineExamAttempt::STATUS_MARKED)
                        <span class="badge">{{ __('ui.results') }}: {{ $attempt->total_marks }}</span>
                        <a href="{{ route('onlineexam.student.result', $attempt) }}" class="btn btn-sm btn-ghost">View</a>
                    @else
                        <span class="badge">{{ __('ui.awaiting_marking') }}</span>
                        <a href="{{ route('onlineexam.student.result', $attempt) }}" class="btn btn-sm btn-ghost">View</a>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
@endif
@endsection
