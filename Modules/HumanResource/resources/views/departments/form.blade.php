@extends('layouts.admin')
@section('title', __('ui.departments'))

@section('content')
<div class="page-head"><h1>{{ $department->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.departments') }}</h1>
    <a href="{{ route('hr.departments.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>

<div class="card" style="max-width:480px">
    <form method="POST" action="{{ $department->exists ? route('hr.departments.update', $department) : route('hr.departments.store') }}">
        @csrf
        @if($department->exists) @method('PUT') @endif
        <label>{{ __('ui.name') }} *</label>
        <input name="name" value="{{ old('name', $department->name) }}" required>
        <div style="margin-top:1.25rem"><button class="btn">{{ $department->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
