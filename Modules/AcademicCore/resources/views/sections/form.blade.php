@extends('layouts.admin')
@section('title', $section->exists ? 'Edit Section' : 'Add Section')

@section('content')
<div class="page-head"><h1>{{ $section->exists ? 'Edit Section' : 'Add Section' }}</h1>
    <a href="{{ route('academic.sections.index') }}" class="btn btn-ghost">Back</a></div>

<div class="card max-w-full" style="max-width:480px">
    <form method="POST"
          action="{{ $section->exists ? route('academic.sections.update', $section) : route('academic.sections.store') }}">
        @csrf
        @if($section->exists) @method('PUT') @endif

        <label>Section name *</label>
        <input name="name" value="{{ old('name', $section->name) }}" required>

        <label>Capacity</label>
        <input name="capacity" type="number" value="{{ old('capacity', $section->capacity) }}">

        <div style="margin-top:1.25rem"><button class="btn">{{ $section->exists ? 'Update' : 'Create' }}</button></div>
    </form>
</div>
@endsection
