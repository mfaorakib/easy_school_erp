@extends('layouts.admin')
@section('title', 'Subjects')

@section('content')
<div class="page-head"><h1>Subjects</h1>
    <a href="{{ route('academic.subjects.create') }}" class="btn">+ Add Subject</a></div>

<div class="card">
    @if($subjects->isEmpty())
        <div class="empty">No subjects yet.</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Name</th><th>Code</th><th>Type</th><th>Pass Mark</th><th></th></tr></thead>
        <tbody>
        @foreach($subjects as $subject)
            <tr>
                <td><strong>{{ $subject->name }}</strong></td>
                <td>{{ $subject->code ?? '—' }}</td>
                <td><span class="badge">{{ ucfirst($subject->type) }}</span></td>
                <td>{{ $subject->pass_mark ?? '—' }}</td>
                <td class="actions">
                    <a href="{{ route('academic.subjects.edit', $subject) }}" class="btn btn-sm btn-ghost">Edit</a>
                    <form method="POST" action="{{ route('academic.subjects.destroy', $subject) }}"
                          onsubmit="return confirm('Delete this subject?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
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
