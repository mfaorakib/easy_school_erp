@extends('layouts.admin')
@section('title', __('ui.class_teacher'))

@section('content')
<div class="page-head"><h1>{{ __('ui.class_teacher') }}</h1></div>

<div class="card">
    <h3 style="margin-top:0">Assign class teacher</h3>
    @if($teachers->isEmpty())
        <div class="empty">No teachers available yet. Add staff with the Teacher role first.</div>
    @else
    <form method="POST" action="{{ route('academic.class-teachers.store') }}">
        @csrf
        <div class="grid">
            <div><label>Class</label>
                <select name="class_id" required>
                    <option value="">—</option>
                    @foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div><label>Section</label>
                <select name="section_id" required>
                    <option value="">—</option>
                    @foreach($sections as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div><label>Teacher(s)</label>
                <select name="teachers[]" multiple size="4">
                    @foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->full_name ?? $t->user->name }}</option>@endforeach
                </select>
            </div>
        </div>
        <div style="margin-top:1rem"><button class="btn">{{ __('ui.assign') }}</button></div>
    </form>
    @endif
</div>

<div class="card">
    <h3 style="margin-top:0">Assigned class teachers</h3>
    @if($assignments->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Class</th><th>Section</th><th>Teacher(s)</th><th></th></tr></thead>
        <tbody>
        @foreach($assignments as $a)
            <tr>
                <td>{{ optional($a->schoolClass)->name }}</td>
                <td>{{ optional($a->section)->name }}</td>
                <td>@foreach($a->teachers as $t)<span class="badge">{{ $t->full_name ?? $t->user->name }}</span> @endforeach</td>
                <td>
                    <form method="POST" action="{{ route('academic.class-teachers.destroy', $a) }}"
                          onsubmit="return confirm('Remove this assignment?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">{{ __('ui.delete') }}</button>
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
