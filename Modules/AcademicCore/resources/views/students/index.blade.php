@extends('layouts.admin')
@section('title', 'Students')

@section('content')
<div class="page-head"><h1>Students</h1>
    <a href="{{ route('academic.students.create') }}" class="btn">+ New Admission</a></div>

<div class="card">
    @if($students->isEmpty())
        <div class="empty">No students admitted yet.</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Adm #</th><th>Name</th><th>Class</th><th>Section</th><th>Roll</th><th>Guardian</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($students as $student)
            @php($rec = $student->liveRecord->first())
            <tr>
                <td>{{ $student->admission_no }}</td>
                <td><strong>{{ $student->full_name }}</strong></td>
                <td>{{ optional(optional($rec)->schoolClass)->name ?? '—' }}</td>
                <td>{{ optional(optional($rec)->section)->name ?? '—' }}</td>
                <td>{{ optional($rec)->roll_no ?? '—' }}</td>
                <td>{{ optional($student->guardian)->guardian_name ?? '—' }}</td>
                <td class="actions">
                    <a href="{{ route('academic.students.show', $student->id) }}" class="btn btn-sm btn-ghost">{{ __('ui.view') }}</a>
                    <a href="{{ route('academic.students.documents.index', $student->id) }}" class="btn btn-sm btn-ghost">{{ __('ui.student_documents') }}</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    <div style="margin-top:1rem">{{ $students->links() }}</div>
    @endif
</div>
@endsection
