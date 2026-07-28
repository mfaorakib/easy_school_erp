@extends('layouts.admin')
@section('title', 'Item Categories')

@section('content')
<div class="page-head"><h1>{{ $category->exists ? __('ui.edit') : __('ui.add') }} — Item Categories</h1>
    <a href="{{ route('inventory.categories.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:480px">
    <form method="POST" action="{{ $category->exists ? route('inventory.categories.update', $category) : route('inventory.categories.store') }}">
        @csrf @if($category->exists) @method('PUT') @endif
        <label>{{ __('ui.name') }} *</label>
        <input name="name" value="{{ old('name', $category->name) }}" required>
        <div style="margin-top:1.25rem"><button class="btn">{{ $category->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
