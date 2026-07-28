@extends('layouts.admin')
@section('title', 'Exam Results')

@section('content')
<div class="page-head"><h1>{{ __('ui.exam_result_report') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('reports.exam.results') }}">
        <div class="grid">
            <div>
                <label>{{ __('ui.exams') }}</label>
                <select name="exam_id">
                    <option value="">—</option>
                    @foreach($exams as $e)
                        <option value="{{ $e->id }}" {{ $examId == $e->id ? 'selected' : '' }}>
                            {{ $e->name }}{{ optional($e->type)->name ? ' — ' . optional($e->type)->name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>{{ __('ui.classes') }}</label>
                <select name="class_id">
                    <option value="">—</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>{{ __('ui.sections') }}</label>
                <select name="section_id">
                    <option value="">—</option>
                    @foreach($sections as $s)
                        <option value="{{ $s->id }}" {{ $sectionId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">{{ __('ui.generate') }}</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    @if(is_null($results))
        <div class="empty">Select exam, class and section, then Generate.</div>
    @elseif($results->isEmpty())
        <div class="empty">{{ __('ui.no_records') }} — results may not be computed yet.</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>{{ __('ui.rank') }}</th>
                <th>Roll</th>
                <th>Student</th>
                <th>Marks</th>
                <th>{{ __('ui.gpa') }}</th>
                <th>{{ __('ui.grade') }}</th>
                <th>Result</th>
            </tr>
        </thead>
        <tbody>
        @foreach($results as $r)
            <tr>
                <td>{{ $r->position }}</td>
                <td>{{ $r->position }}</td>
                <td>{{ optional($r->student)->full_name ?? '—' }}</td>
                <td>{{ $r->total_marks }} / {{ $r->total_full_mark }}</td>
                <td>{{ $r->gpa }}</td>
                <td><span class="badge">{{ $r->grade ?: '—' }}</span></td>
                <td><span class="badge">{{ $r->is_pass ? __('ui.pass') : __('ui.fail') }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
