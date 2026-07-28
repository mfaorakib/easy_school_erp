@extends('layouts.admin')
@section('title', $subject->exists ? 'Edit Subject' : 'Add Subject')

@section('content')
<div class="page-head"><h1>{{ $subject->exists ? 'Edit Subject' : 'Add Subject' }}</h1>
    <a href="{{ route('academic.subjects.index') }}" class="btn btn-ghost">Back</a></div>

<div class="card max-w-full" style="max-width:560px">
    <form method="POST"
          action="{{ $subject->exists ? route('academic.subjects.update', $subject) : route('academic.subjects.store') }}">
        @csrf
        @if($subject->exists) @method('PUT') @endif

        <div class="grid">
            <div>
                <label>Subject name *</label>
                <input name="name" value="{{ old('name', $subject->name) }}" required>
            </div>
            <div>
                <label>Code</label>
                <input name="code" value="{{ old('code', $subject->code) }}">
            </div>
            <div>
                <label>Type *</label>
                <select name="type">
                    <option value="theory" {{ old('type', $subject->type) === 'theory' ? 'selected' : '' }}>Theory</option>
                    <option value="practical" {{ old('type', $subject->type) === 'practical' ? 'selected' : '' }}>Practical</option>
                </select>
            </div>
            <div>
                <label>Pass mark</label>
                <input name="pass_mark" type="number" step="any" value="{{ old('pass_mark', $subject->pass_mark) }}">
            </div>
        </div>

        <div style="margin-top:1.25rem"><button class="btn">{{ $subject->exists ? 'Update' : 'Create' }}</button></div>
    </form>
</div>
@endsection
