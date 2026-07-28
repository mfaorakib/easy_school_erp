@extends('layouts.admin')
@section('title', 'Online Exams')

@section('content')
<div class="page-head"><h1>{{ __('ui.online_exam') }} — {{ __('ui.exams') }}</h1>
    <a href="{{ route('onlineexam.exams.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>

<div class="card">
    @if($exams->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>{{ __('ui.name') }}</th>
            <th>{{ __('ui.classes') }} / {{ __('ui.sections') }}</th>
            <th>{{ __('ui.subjects') }}</th>
            <th>Date</th>
            <th>{{ __('ui.questions') }}</th>
            <th>{{ __('ui.published') }}</th>
            <th>{{ __('ui.actions') }}</th>
        </tr></thead>
        <tbody>
        @foreach($exams as $exam)
            <tr>
                <td><strong>{{ $exam->title }}</strong></td>
                <td>{{ optional($exam->schoolClass)->name }} / {{ optional($exam->section)->name }}</td>
                <td>{{ optional($exam->subject)->name ?: '—' }}</td>
                <td>{{ $exam->exam_date ? $exam->exam_date->format('d M Y') : '—' }}</td>
                <td><span class="badge">{{ $exam->questions_count }}</span> · {{ $exam->totalMarks() }} {{ __('ui.marks') }}</td>
                <td><span class="badge">{{ $exam->is_published ? __('ui.true') : __('ui.false') }}</span></td>
                <td class="actions">
                    <a href="{{ route('onlineexam.exams.edit', $exam) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <a href="{{ route('onlineexam.exams.questions', $exam) }}" class="btn btn-sm btn-ghost">{{ __('ui.questions') }}</a>
                    <form method="POST" action="{{ route('onlineexam.exams.destroy', $exam) }}" onsubmit="return confirm('Delete?')" style="display:inline">
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
