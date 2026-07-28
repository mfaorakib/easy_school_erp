@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
@php
    use Modules\AcademicCore\Models\SchoolClass;
    use Modules\AcademicCore\Models\Section;
    use Modules\AcademicCore\Models\Subject;
    use Modules\AcademicCore\Models\Student;
@endphp

<div class="page-head"><h1>Welcome, {{ auth()->user()->name }}</h1></div>

<div class="stat-grid">
    <div class="stat"><div class="n">{{ Student::where('is_active', true)->count() }}</div><div class="l">Active Students</div></div>
    <div class="stat"><div class="n">{{ SchoolClass::count() }}</div><div class="l">Classes</div></div>
    <div class="stat"><div class="n">{{ Section::count() }}</div><div class="l">Sections</div></div>
    <div class="stat"><div class="n">{{ Subject::count() }}</div><div class="l">Subjects</div></div>
</div>

<div class="card" style="margin-top:1.25rem">
    <h3 style="margin-top:0">Quick start</h3>
    <p class="l" style="color:var(--muted)">Set up your academic structure, then admit students.</p>
    <div class="actions" style="flex-wrap:wrap">
        <a href="{{ route('academic.sections.create') }}" class="btn btn-ghost btn-sm">Add Section</a>
        <a href="{{ route('academic.classes.create') }}" class="btn btn-ghost btn-sm">Add Class</a>
        <a href="{{ route('academic.subjects.create') }}" class="btn btn-ghost btn-sm">Add Subject</a>
        <a href="{{ route('academic.students.create') }}" class="btn btn-sm">New Admission</a>
    </div>
    <p class="l" style="color:var(--muted);margin-top:1rem">
        Role: {{ auth()->user()->getRoleNames()->implode(', ') ?: '—' }} ·
        Academic year: {{ optional(\Modules\Foundation\Models\AcademicYear::current())->title ?? '—' }}
    </p>
</div>
@endsection
