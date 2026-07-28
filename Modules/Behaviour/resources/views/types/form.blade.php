@extends('layouts.admin')
@section('title', 'Behaviour Types')

@section('content')
<div class="page-head"><h1>{{ $type->exists ? __('ui.edit') : __('ui.add') }} — Behaviour Types</h1>
    <a href="{{ route('behaviour.types.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:520px">
    <form method="POST" action="{{ $type->exists ? route('behaviour.types.update', $type) : route('behaviour.types.store') }}">
        @csrf @if($type->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.name') }} *</label><input name="title" value="{{ old('title', $type->title) }}" required></div>
            <div><label>Point *</label><input name="point" type="number" step="any" value="{{ old('point', $type->point) }}" required></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $type->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
