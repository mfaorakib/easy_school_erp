@extends('layouts.admin')
@section('title', 'Download Content')

@php
    $audienceOptions = ['all' => 'Everyone', 'admin' => 'Admin', 'teacher' => 'Teacher', 'student' => 'Student', 'parent' => 'Parent'];
    $selectedAudiences = old('audiences', $content->audiences ?? []);
@endphp

@section('content')
<div class="page-head"><h1>{{ $content->exists ? __('ui.edit') : __('ui.add') }} — Download Content</h1>
    <a href="{{ route('downloadcenter.contents.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" enctype="multipart/form-data" action="{{ $content->exists ? route('downloadcenter.contents.update', $content) : route('downloadcenter.contents.store') }}">
        @csrf @if($content->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>Type</label>
                <select name="content_type_id">
                    <option value="">—</option>
                    @foreach($types as $t)<option value="{{ $t->id }}" {{ old('content_type_id', $content->content_type_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach
                </select>
            </div>
            <div><label>Title *</label><input name="title" value="{{ old('title', $content->title) }}" required></div>
            <div><label>Description</label><textarea name="description" rows="4">{{ old('description', $content->description) }}</textarea></div>
            <div><label>{{ __('ui.classes') }}</label>
                <select name="class_id">
                    <option value="">Any</option>
                    @foreach($classes as $c)<option value="{{ $c->id }}" {{ old('class_id', $content->class_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div><label>Section</label>
                <select name="section_id">
                    <option value="">Any</option>
                    @foreach($sections as $s)<option value="{{ $s->id }}" {{ old('section_id', $content->section_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div><label>Audience</label>
                @foreach($audienceOptions as $value => $text)
                    <label style="display:inline-flex;align-items:center;gap:.35rem;margin-right:1rem;font-weight:normal">
                        <input type="checkbox" name="audiences[]" value="{{ $value }}" {{ in_array($value, $selectedAudiences) ? 'checked' : '' }}> {{ $text }}
                    </label>
                @endforeach
            </div>
            <div><label>External URL</label><input name="external_url" type="url" value="{{ old('external_url', $content->external_url) }}"></div>
            <div><label>File</label>
                <input name="file" type="file">
                @if($content->exists && $content->link())
                    <div style="margin-top:.35rem;font-size:.85rem">Current: <a href="{{ $content->link() }}" target="_blank">Open</a></div>
                @endif
            </div>
            <div><label>Publish Date</label><input name="publish_date" type="date" value="{{ old('publish_date', optional($content->publish_date)->format('Y-m-d')) }}"></div>
            <div><label style="display:inline-flex;align-items:center;gap:.35rem;font-weight:normal">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', $content->exists ? $content->is_published : true) ? 'checked' : '' }}> Published
            </label></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $content->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
