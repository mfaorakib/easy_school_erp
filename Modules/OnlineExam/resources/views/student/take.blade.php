@extends('layouts.admin')
@section('title', $exam->title)

@section('content')
<div class="page-head">
    <h1>{{ $exam->title }} <span class="badge">{{ __('ui.take_exam') }}</span></h1>
    <a href="{{ route('onlineexam.student.index') }}" class="btn btn-sm btn-ghost">{{ __('ui.back') }}</a>
</div>

<div class="card">
    <div class="grid">
        <div><strong>{{ __('ui.total_marks') }}:</strong> {{ $exam->totalMarks() }}</div>
        @if($exam->duration_minutes)
            <div><strong>Duration:</strong> {{ $exam->duration_minutes }} min</div>
        @endif
    </div>
</div>

@if($exam->instruction)
<div class="card">
    <h1>{{ __('ui.instruction') }}</h1>
    <p>{{ $exam->instruction }}</p>
</div>
@endif

<form method="POST" action="{{ route('onlineexam.student.submit', $exam) }}">
    @csrf
    @foreach($exam->questions as $q)
    <div class="card">
        <div class="page-head">
            <h1>Q{{ $loop->iteration }}. {{ $q->question }}</h1>
            <span class="badge">{{ $q->marks }} {{ __('ui.marks') }}</span>
        </div>

        @if($q->isMcq())
            @foreach($q->options as $opt)
                <div>
                    <label>
                        <input type="checkbox" name="answers[{{ $q->id }}][options][]" value="{{ $opt->id }}">
                        {{ $opt->title }}
                    </label>
                </div>
            @endforeach
        @elseif($q->isTrueFalse())
            <div>
                <label>
                    <input type="radio" name="answers[{{ $q->id }}][bool]" value="1">
                    {{ __('ui.true') }}
                </label>
            </div>
            <div>
                <label>
                    <input type="radio" name="answers[{{ $q->id }}][bool]" value="0">
                    {{ __('ui.false') }}
                </label>
            </div>
        @elseif($q->isFill())
            <div>
                <input type="text" name="answers[{{ $q->id }}][text]">
            </div>
        @endif
    </div>
    @endforeach

    <div class="card">
        <button type="submit" class="btn">{{ __('ui.submit_exam') }}</button>
        <a href="{{ route('onlineexam.student.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a>
    </div>
</form>
@endsection
