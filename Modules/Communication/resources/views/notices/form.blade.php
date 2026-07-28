@extends('layouts.admin')
@section('title', 'Notice Board')

@php
    $audienceOptions = ['all' => 'Everyone', 'admin' => 'Admin', 'teacher' => 'Teacher', 'student' => 'Student', 'parent' => 'Parent'];
    $selectedAudiences = old('audiences', $notice->audiences ?? []);
@endphp

@section('content')
<div class="page-head"><h1>{{ $notice->exists ? __('ui.edit') : __('ui.add') }} — Notice Board</h1>
    <a href="{{ route('communication.notices.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $notice->exists ? route('communication.notices.update', $notice) : route('communication.notices.store') }}">
        @csrf @if($notice->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>Title *</label><input name="title" value="{{ old('title', $notice->title) }}" required></div>
            <div><label>Description</label><textarea name="description" rows="4">{{ old('description', $notice->description) }}</textarea></div>
            <div><label>Notice Date *</label><input name="notice_date" type="date" value="{{ old('notice_date', optional($notice->notice_date)->format('Y-m-d') ?: date('Y-m-d')) }}" required></div>
            <div><label>Publish Date</label><input name="publish_date" type="date" value="{{ old('publish_date', optional($notice->publish_date)->format('Y-m-d')) }}"></div>
            <div><label>Audience *</label>
                @foreach($audienceOptions as $value => $text)
                    <label style="display:inline-flex;align-items:center;gap:.35rem;margin-right:1rem;font-weight:normal">
                        <input type="checkbox" name="audiences[]" value="{{ $value }}" {{ in_array($value, $selectedAudiences) ? 'checked' : '' }}> {{ $text }}
                    </label>
                @endforeach
            </div>
            <div><label style="display:inline-flex;align-items:center;gap:.35rem;font-weight:normal">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', $notice->exists ? $notice->is_published : true) ? 'checked' : '' }}> Published
            </label></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $notice->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
