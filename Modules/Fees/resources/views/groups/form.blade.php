@extends('layouts.admin')
@section('title', __('ui.fee_groups'))

@section('content')
<div class="page-head"><h1>{{ $group->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.fee_groups') }}</h1>
    <a href="{{ route('fees.groups.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:520px">
    <form method="POST" action="{{ $group->exists ? route('fees.groups.update', $group) : route('fees.groups.store') }}">
        @csrf @if($group->exists) @method('PUT') @endif
        <label>{{ __('ui.name') }} *</label>
        <input name="name" value="{{ old('name', $group->name) }}" required>
        <label>Description</label>
        <input name="description" value="{{ old('description', $group->description) }}">
        <div style="margin-top:1.25rem"><button class="btn">{{ $group->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
