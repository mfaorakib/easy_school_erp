@extends('layouts.admin')
@section('title', 'Assign Questions')

@section('content')
<div class="page-head"><h1>{{ $exam->title }} — {{ __('ui.assign_questions') }}</h1>
    <a href="{{ route('onlineexam.exams.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>

<div class="card">
    @if($available->isEmpty())
        <div class="empty">No questions in the bank for this class. Add questions to the {{ __('ui.question_bank') }} first.</div>
    @else
    <form method="POST" action="{{ route('onlineexam.exams.questions.sync', $exam) }}">
        @csrf @method('PUT')
        <div class="overflow-x-auto">
        <table>
            <thead><tr>
                <th></th>
                <th>{{ __('ui.questions') }}</th>
                <th>Type</th>
                <th>{{ __('ui.question_groups') }}</th>
                <th>{{ __('ui.marks') }}</th>
            </tr></thead>
            <tbody>
            @foreach($available as $q)
                <tr>
                    <td><input type="checkbox" name="question_ids[]" value="{{ $q->id }}" {{ in_array($q->id, $assignedIds) ? 'checked' : '' }}></td>
                    <td>{{ \Illuminate\Support\Str::limit($q->question, 80) }}</td>
                    <td><span class="badge">
                        @if($q->isMcq()) {{ __('ui.mcq') }}
                        @elseif($q->isTrueFalse()) {{ __('ui.true_false') }}
                        @else {{ __('ui.fill_blank') }}
                        @endif
                    </span></td>
                    <td>{{ optional($q->group)->name ?: '—' }}</td>
                    <td>{{ $q->marks }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ __('ui.save') }}</button></div>
    </form>
    @endif
</div>
@endsection
