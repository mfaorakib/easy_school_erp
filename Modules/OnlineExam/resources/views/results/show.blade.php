@extends('layouts.admin')
@section('title', 'Exam Result')

@section('content')
<div class="page-head">
    <h1>
        {{ optional($attempt->student)->full_name }} — {{ optional($attempt->exam)->title }}
        <span class="badge">{{ $attempt->total_marks }} / {{ $attempt->exam->totalMarks() }}</span>
        @if($attempt->awaitsMarking())
            <span class="badge">{{ __('ui.awaiting_marking') }}</span>
        @elseif($attempt->status === \Modules\OnlineExam\Models\OnlineExamAttempt::STATUS_MARKED)
            <span class="badge">Marked</span>
        @elseif($attempt->status === \Modules\OnlineExam\Models\OnlineExamAttempt::STATUS_SUBMITTED)
            <span class="badge">Submitted</span>
        @else
            <span class="badge">{{ __('ui.pending') }}</span>
        @endif
    </h1>
    <a href="{{ route('onlineexam.results.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('onlineexam.results.mark', $attempt) }}">
        @csrf @method('PUT')
        <div class="overflow-x-auto">
        <table>
            <thead><tr>
                <th>{{ __('ui.questions') }}</th>
                <th>{{ __('ui.your_answer') }}</th>
                <th>{{ __('ui.status') }}</th>
                <th>{{ __('ui.marks') }}</th>
            </tr></thead>
            <tbody>
            @foreach($attempt->answers as $answer)
                @php $question = $answer->question; @endphp
                <tr>
                    <td>
                        {{ $question->question }}
                        <div>
                            <span class="badge">
                                @if($question->isMcq()) {{ __('ui.mcq') }}
                                @elseif($question->isTrueFalse()) {{ __('ui.true_false') }}
                                @else {{ __('ui.fill_blank') }}
                                @endif
                            </span>
                            <span class="badge">{{ __('ui.marks') }}: {{ $question->marks }}</span>
                        </div>
                    </td>
                    <td>
                        @if($question->isMcq())
                            @php
                                $selected = $answer->selected_options ?: [];
                                $titles = $question->options->whereIn('id', $selected)->pluck('title')->all();
                            @endphp
                            {{ $titles ? implode(', ', $titles) : '—' }}
                        @elseif($question->isTrueFalse())
                            {{ $answer->bool_answer === null ? '—' : ($answer->bool_answer ? __('ui.true') : __('ui.false')) }}
                        @else
                            {{ $answer->text_answer ?: '—' }}
                        @endif
                    </td>
                    <td>
                        @if($answer->is_correct === true)
                            <span class="badge">Correct</span>
                        @elseif($answer->is_correct === false)
                            <span class="badge">Wrong</span>
                        @endif
                    </td>
                    <td>
                        @if($question->isFill() || $answer->obtain_marks === null)
                            <input type="number" step="0.5" min="0" max="{{ $question->marks }}" name="marks[{{ $answer->id }}]" value="{{ $answer->obtain_marks }}" style="width:6rem">
                            <span>/ {{ $question->marks }}</span>
                        @else
                            {{ $answer->obtain_marks }} / {{ $question->marks }}
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ __('ui.mark_answer') }}</button></div>
    </form>
</div>
@endsection
