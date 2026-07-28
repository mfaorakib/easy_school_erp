@extends('layouts.admin')
@section('title', 'Student Promotion')

@section('content')
<div class="page-head"><h1>Student Promotion</h1></div>

{{-- Step 1: pick the current class/section to load its live students --}}
<div class="card">
    <h3 style="margin-top:0">1. Select current class &amp; section</h3>
    <form method="GET" action="{{ route('academic.promotion.create') }}">
        <div class="grid">
            <div><label>From class</label>
                <select name="from_class">
                    <option value="">—</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $fromClass == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>From section</label>
                <select name="from_section">
                    <option value="">—</option>
                    @foreach($sections as $s)
                        <option value="{{ $s->id }}" {{ $fromSection == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;align-items:flex-end"><button class="btn">Load Students</button></div>
        </div>
    </form>
</div>

{{-- Step 2: choose target & promote --}}
@if($fromClass && $fromSection)
<div class="card">
    <h3 style="margin-top:0">2. Promote to</h3>
    @if($students->isEmpty())
        <div class="empty">No live students found in this class/section.</div>
    @else
    <form method="POST" action="{{ route('academic.promotion.store') }}">
        @csrf
        <div class="grid">
            <div><label>To year *</label>
                <select name="to_year_id" required>
                    @foreach($years as $y)
                        <option value="{{ $y->id }}">{{ $y->title }} {{ $y->is_active ? '(current)' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>To class *</label>
                <select name="to_class_id" required>
                    @foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div><label>To section *</label>
                <select name="to_section_id" required>
                    @foreach($sections as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
        <table style="margin-top:1rem">
            <thead><tr>
                <th><input type="checkbox" onclick="document.querySelectorAll('.pick').forEach(c=>c.checked=this.checked)"></th>
                <th>Adm #</th><th>Name</th>
            </tr></thead>
            <tbody>
            @foreach($students as $student)
                <tr>
                    <td><input class="pick" type="checkbox" name="student_ids[]" value="{{ $student->id }}" checked style="width:auto"></td>
                    <td>{{ $student->admission_no }}</td>
                    <td>{{ $student->full_name }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>

        <div style="margin-top:1.25rem"><button class="btn">Promote Selected</button></div>
    </form>
    @endif
</div>
@endif
@endsection
