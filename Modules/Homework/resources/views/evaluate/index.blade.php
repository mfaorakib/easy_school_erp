@extends('layouts.admin')
@section('title', __('ui.homework_evaluation'))

@section('content')
<div class="page-head"><h1>{{ __('ui.homework_evaluation') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('homework.evaluate.index') }}">
        <div class="grid">
            <div><label>{{ __('ui.homework') }}</label>
                <select name="homework_id" onchange="this.form.submit()"><option value="">—</option>
                    @foreach($homeworks as $hw)
                        <option value="{{ $hw->id }}" {{ $homework && $homework->id == $hw->id ? 'selected' : '' }}>{{ $hw->title }} — {{ optional($hw->schoolClass)->name }}/{{ optional($hw->section)->name }} · {{ optional($hw->subject)->name }}</option>
                    @endforeach
                </select></div>
            <div style="align-self:end"><button class="btn btn-sm">{{ __('ui.load') }}</button></div>
        </div>
    </form>
</div>

@if($homework)
<div class="card">
    <p class="empty" style="text-align:left;color:var(--muted);padding:0 0 .5rem">{{ $homework->title }} · Max marks: {{ $homework->evaluation_marks !== null ? (int) $homework->evaluation_marks : '—' }}</p>
    @if(empty($roster))<div class="empty">{{ __('ui.no_records') }}</div>@else
    <form method="POST" action="{{ route('homework.evaluate.store') }}">
        @csrf
        <input type="hidden" name="homework_id" value="{{ $homework->id }}">
        <div class="overflow-x-auto">
        <table>
            <thead><tr><th>Roll</th><th>{{ __('ui.name') }}</th><th>Complete</th><th>Marks</th><th>Comment</th></tr></thead>
            <tbody>
            @foreach($roster as $row)
                <tr>
                    <td>{{ $row['roll_no'] ?? '—' }}</td>
                    <td>{{ $row['student']->full_name }}</td>
                    <td><input type="checkbox" name="complete[{{ $row['student']->id }}]" value="1" {{ optional($row['eval'])->is_complete ? 'checked' : '' }} style="width:auto"></td>
                    <td><input type="number" step="any" name="marks[{{ $row['student']->id }}]" value="{{ optional($row['eval'])->obtained_marks }}" style="width:100px" max="{{ $homework->evaluation_marks }}"></td>
                    <td><input type="text" name="comment[{{ $row['student']->id }}]" value="{{ optional($row['eval'])->comment }}" style="width:100%"></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ __('ui.save') }}</button></div>
    </form>
    @endif
</div>
@else
<div class="card"><div class="empty">{{ __('ui.no_records') }}</div></div>
@endif
@endsection
