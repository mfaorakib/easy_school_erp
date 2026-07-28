@extends('layouts.admin')
@section('title', 'Question Groups')

@section('content')
<div class="page-head"><h1>{{ $group->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.question_groups') }}</h1>
    <a href="{{ route('onlineexam.groups.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $group->exists ? route('onlineexam.groups.update', $group) : route('onlineexam.groups.store') }}">
        @csrf @if($group->exists) @method('PUT') @endif
        <div class="grid">
            <div style="grid-column:1/-1"><label>{{ __('ui.name') }} *</label>
                <input name="title" type="text" maxlength="200" value="{{ old('title', $group->title) }}" required></div>
            <div style="grid-column:1/-1"><label>
                <input name="is_active" type="checkbox" value="1" {{ old('is_active', $group->exists ? $group->is_active : true) ? 'checked' : '' }}> Active</label></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $group->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
