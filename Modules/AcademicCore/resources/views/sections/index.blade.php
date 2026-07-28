@extends('layouts.admin')
@section('title', 'Sections')

@section('content')
<div class="page-head"><h1>Sections</h1>
    <a href="{{ route('academic.sections.create') }}" class="btn">+ Add Section</a></div>

<div class="card">
    @if($sections->isEmpty())
        <div class="empty">No sections yet.</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Name</th><th>Capacity</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach($sections as $section)
            <tr>
                <td><strong>{{ $section->name }}</strong></td>
                <td>{{ $section->capacity ?? '—' }}</td>
                <td><span class="badge">{{ $section->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="actions">
                    <a href="{{ route('academic.sections.edit', $section) }}" class="btn btn-sm btn-ghost">Edit</a>
                    <form method="POST" action="{{ route('academic.sections.destroy', $section) }}"
                          onsubmit="return confirm('Delete this section?')">
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
