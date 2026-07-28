@extends('layouts.admin')
@section('title', __('ui.designations'))

@section('content')
<div class="page-head"><h1>{{ $designation->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.designations') }}</h1>
    <a href="{{ route('hr.designations.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>

<div class="card" style="max-width:480px">
    <form method="POST" action="{{ $designation->exists ? route('hr.designations.update', $designation) : route('hr.designations.store') }}">
        @csrf
        @if($designation->exists) @method('PUT') @endif
        <label>{{ __('ui.name') }} *</label>
        <input name="title" value="{{ old('title', $designation->title) }}" required>
        <div style="margin-top:1.25rem"><button class="btn">{{ $designation->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
