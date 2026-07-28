@extends('layouts.admin')
@section('title', 'Classes')

@section('content')
<div class="page-head">
    <h1>Classes</h1>
    <a href="{{ route('academic.classes.create') }}" class="btn">+ Add Class</a>
</div>

<div class="card">
    @if($classes->isEmpty())
        <div class="empty">No classes yet. Create your first class.</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Name</th><th>Sections</th><th>Pass Mark</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach($classes as $class)
            <tr>
                <td><strong>{{ $class->name }}</strong></td>
                <td>
                    @forelse($class->sections as $s)<span class="badge">{{ $s->name }}</span> @empty
                        <span class="badge">—</span>@endforelse
                </td>
                <td>{{ $class->pass_mark ?? '—' }}</td>
                <td>{!! $class->is_active ? '<span class="badge">Active</span>' : '<span class="badge">Inactive</span>' !!}</td>
                <td class="actions">
                    <a href="{{ route('academic.classes.edit', $class) }}" class="btn btn-sm btn-ghost">Edit</a>
                    <form method="POST" action="{{ route('academic.classes.destroy', $class) }}"
                          onsubmit="return confirm('Delete this class?')">
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
