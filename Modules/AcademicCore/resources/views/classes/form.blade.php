@extends('layouts.admin')
@section('title', $class->exists ? 'Edit Class' : 'Add Class')

@section('content')
<div class="page-head"><h1>{{ $class->exists ? 'Edit Class' : 'Add Class' }}</h1>
    <a href="{{ route('academic.classes.index') }}" class="btn btn-ghost">Back</a></div>

<div class="card max-w-full" style="max-width:640px">
    <form method="POST"
          action="{{ $class->exists ? route('academic.classes.update', $class) : route('academic.classes.store') }}">
        @csrf
        @if($class->exists) @method('PUT') @endif

        <div class="grid">
            <div>
                <label>Class name *</label>
                <input name="name" value="{{ old('name', $class->name) }}" required>
            </div>
            <div>
                <label>Pass mark</label>
                <input name="pass_mark" type="number" step="any" value="{{ old('pass_mark', $class->pass_mark) }}">
            </div>
            <div>
                <label>Position</label>
                <input name="position" type="number" value="{{ old('position', $class->position ?? 0) }}">
            </div>
        </div>

        <label>Sections</label>
        <div class="grid">
            @forelse($sections as $section)
                <label class="badge" style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                    <input type="checkbox" name="sections[]" value="{{ $section->id }}" style="width:auto"
                        {{ in_array($section->id, old('sections', $selected)) ? 'checked' : '' }}>
                    {{ $section->name }}
                </label>
            @empty
                <p class="empty">No sections yet — create sections first.</p>
            @endforelse
        </div>

        <div style="margin-top:1.25rem"><button class="btn">{{ $class->exists ? 'Update' : 'Create' }} Class</button></div>
    </form>
</div>
@endsection
