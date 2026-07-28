@extends('layouts.admin')
@section('title', __('ui.book_categories'))

@section('content')
<div class="page-head"><h1>{{ $category->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.book_categories') }}</h1>
    <a href="{{ route('library.categories.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:480px">
    <form method="POST" action="{{ $category->exists ? route('library.categories.update', $category) : route('library.categories.store') }}">
        @csrf @if($category->exists) @method('PUT') @endif
        <label>{{ __('ui.name') }} *</label>
        <input name="name" value="{{ old('name', $category->name) }}" required>
        <div style="margin-top:1.25rem"><button class="btn">{{ $category->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
