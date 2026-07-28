@extends('layouts.admin')
@section('title', 'Question Bank')

@section('content')
<div class="page-head"><h1>{{ __('ui.question_bank') }}</h1>
    <a href="{{ route('onlineexam.questions.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>

<div class="card">
    @if($questions->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Question</th><th>Type</th><th>{{ __('ui.question_groups') }}</th><th>{{ __('ui.classes') }}</th><th>{{ __('ui.marks') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($questions as $q)
            <tr>
                <td><strong>{{ \Illuminate\Support\Str::limit($q->question, 60) }}</strong></td>
                <td>
                    @switch($q->type)
                        @case('mcq')<span class="badge">MCQ</span>@break
                        @case('truefalse')<span class="badge">True·False</span>@break
                        @case('fill')<span class="badge">Fill</span>@break
                    @endswitch
                </td>
                <td>{{ optional($q->group)->title ?? '—' }}</td>
                <td>{{ optional($q->schoolClass)->name ?? 'All' }}</td>
                <td>{{ $q->marks }}</td>
                <td class="actions">
                    <a href="{{ route('onlineexam.questions.edit', $q) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('onlineexam.questions.destroy', $q) }}" onsubmit="return confirm('Delete?')">
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
