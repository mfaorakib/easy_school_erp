@extends('layouts.admin')
@section('title', __('ui.results'))

@section('content')
<div class="page-head">
    <h1>{{ $attempt->exam->title }} <span class="badge">{{ __('ui.results') }}</span></h1>
    <a href="{{ route('onlineexam.student.index') }}" class="btn btn-sm btn-ghost">{{ __('ui.back') }}</a>
</div>

<div class="card">
    <div class="page-head">
        <h1>
            <span class="badge">{{ ucfirst($attempt->status) }}</span>
            {{ $attempt->total_marks }} / {{ $attempt->exam->totalMarks() }}
        </h1>
    </div>
    @if($attempt->awaitsMarking())
        <p class="badge">{{ __('ui.awaiting_marking') }}</p>
    @endif
</div>

@foreach($attempt->answers as $answer)
<div class="card">
    <div class="page-head">
        <h1>{{ optional($answer->question)->question }}</h1>
        <span class="badge">{{ optional($answer->question)->type }}</span>
    </div>

    <p>
        <strong>{{ __('ui.your_answer') }}:</strong>
        @php($q = $answer->question)
        @if($q && $q->isMcq())
            @php($selected = collect($answer->selected_options ?? []))
            {{ $q->options->whereIn('id', $selected)->pluck('title')->implode(', ') ?: '—' }}
        @elseif($q && $q->isTrueFalse())
            {{ ! is_null($answer->bool_answer) ? ($answer->bool_answer ? __('ui.true') : __('ui.false')) : '—' }}
        @else
            {{ $answer->text_answer ?: '—' }}
        @endif
    </p>

    <p>
        <strong>{{ __('ui.marks') }}:</strong>
        @if(is_null($answer->obtain_marks))
            <span class="badge">{{ __('ui.awaiting_marking') }}</span>
        @else
            {{ $answer->obtain_marks }} / {{ optional($answer->question)->marks }}
        @endif
        @if(! is_null($answer->is_correct))
            @if($answer->is_correct)
                <span class="badge">{{ __('ui.correct_answer') }}</span>
            @else
                <span class="badge">Wrong</span>
            @endif
        @endif
    </p>
</div>
@endforeach
@endsection
