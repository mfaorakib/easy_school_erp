@extends('layouts.admin')
@section('title', 'Pages')

@section('content')
<div class="page-head"><h1>{{ $page->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.pages') }}</h1>
    <a href="{{ route('builder.pages.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $page->exists ? route('builder.pages.update', $page) : route('builder.pages.store') }}">
        @csrf @if($page->exists) @method('PUT') @endif
        <div class="grid">
            <div style="grid-column:1/-1"><label>{{ __('ui.page_title') }} *</label>
                <input name="title" type="text" maxlength="255" value="{{ old('title', $page->title) }}" required></div>

            @if($page->exists)
            <div style="grid-column:1/-1"><label>{{ __('ui.slug') }}</label>
                <input name="slug" type="text" value="{{ old('slug', $page->slug) }}">
                <small>Leave blank to keep the current slug.</small></div>
            @endif

            <div style="grid-column:1/-1"><label>Meta description</label>
                <textarea name="meta_description" rows="3" maxlength="255">{{ old('meta_description', $page->meta_description) }}</textarea></div>

            <div style="grid-column:1/-1">
                <label><input type="checkbox" name="is_published" value="1"
                    {{ old('is_published', $page->exists ? $page->is_published : true) ? 'checked' : '' }}> {{ __('ui.published') }}</label>
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $page->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
