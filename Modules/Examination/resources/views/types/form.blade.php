@extends('layouts.admin')
@section('title', __('ui.exam_types'))

@section('content')
<div class="page-head"><h1>{{ $type->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.exam_types') }}</h1>
    <a href="{{ route('exam.types.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:480px">
    <form method="POST" action="{{ $type->exists ? route('exam.types.update', $type) : route('exam.types.store') }}">
        @csrf @if($type->exists) @method('PUT') @endif
        <label>{{ __('ui.name') }} *</label>
        <input name="name" value="{{ old('name', $type->name) }}" required>
        <div style="margin-top:1.25rem"><button class="btn">{{ $type->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
