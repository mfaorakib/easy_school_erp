@extends('layouts.admin')
@section('title', __('ui.results'))

@section('content')
<div class="page-head"><h1>{{ __('ui.results') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('exam.results.index') }}">
        <div class="grid">
            <div><label>{{ __('ui.exams') }}</label>
                <select name="exam_id"><option value="">—</option>
                    @foreach($exams as $e)<option value="{{ $e->id }}" {{ $examId == $e->id ? 'selected' : '' }}>{{ $e->name }}</option>@endforeach
                </select></div>
            <div><label>{{ __('ui.classes') }}</label>
                <select name="class_id"><option value="">—</option>
                    @foreach($classes as $c)<option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                </select></div>
            <div><label>{{ __('ui.sections') }}</label>
                <select name="section_id"><option value="">—</option>
                    @foreach($sections as $s)<option value="{{ $s->id }}" {{ $sectionId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
                </select></div>
            <div style="display:flex;align-items:flex-end;gap:.5rem"><button class="btn">{{ __('ui.load') }}</button></div>
        </div>
    </form>
    @if($examId && $classId && $sectionId)
        <form method="POST" action="{{ route('exam.results.compute') }}" style="margin-top:1rem">
            @csrf
            <input type="hidden" name="exam_id" value="{{ $examId }}">
            <input type="hidden" name="class_id" value="{{ $classId }}">
            <input type="hidden" name="section_id" value="{{ $sectionId }}">
            <button class="btn">{{ __('ui.compute') }}</button>
        </form>
    @endif
</div>

@if($examId && $classId && $sectionId)
<div class="card">
    @if($results->isEmpty())
        <div class="empty">No results yet — enter marks then compute.</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>{{ __('ui.position') }}</th><th>{{ __('ui.name') }}</th><th>Total</th>
            <th>{{ __('ui.gpa') }}</th><th>{{ __('ui.grade') }}</th><th>{{ __('ui.result') }}</th>
        </tr></thead>
        <tbody>
        @foreach($results as $r)
            <tr>
                <td>{{ $r->position }}</td>
                <td>{{ optional($r->student)->full_name }}</td>
                <td>{{ (int) $r->total_marks }} / {{ (int) $r->total_full_mark }}</td>
                <td>{{ number_format($r->gpa, 2) }}</td>
                <td><span class="badge">{{ $r->grade ?? '—' }}</span></td>
                <td>{!! $r->is_pass ? '<span class="badge" style="color:var(--success)">'.__('ui.pass').'</span>' : '<span class="badge" style="color:var(--danger)">'.__('ui.fail').'</span>' !!}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endif
@endsection
