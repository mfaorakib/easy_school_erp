@extends('layouts.admin')
@section('title', 'Content Types')

@section('content')
<div class="page-head"><h1>{{ $type->exists ? __('ui.edit') : __('ui.add') }} — Content Types</h1>
    <a href="{{ route('downloadcenter.types.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:480px">
    <form method="POST" action="{{ $type->exists ? route('downloadcenter.types.update', $type) : route('downloadcenter.types.store') }}">
        @csrf @if($type->exists) @method('PUT') @endif
        <label>{{ __('ui.name') }} *</label>
        <input name="name" value="{{ old('name', $type->name) }}" required>
        <div style="margin-top:1.25rem"><button class="btn">{{ $type->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
