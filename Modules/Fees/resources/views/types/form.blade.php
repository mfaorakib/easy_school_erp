@extends('layouts.admin')
@section('title', __('ui.fee_types'))

@section('content')
<div class="page-head"><h1>{{ $type->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.fee_types') }}</h1>
    <a href="{{ route('fees.types.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:520px">
    <form method="POST" action="{{ $type->exists ? route('fees.types.update', $type) : route('fees.types.store') }}">
        @csrf @if($type->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.name') }} *</label><input name="name" value="{{ old('name', $type->name) }}" required></div>
            <div><label>Code</label><input name="code" value="{{ old('code', $type->code) }}"></div>
        </div>
        <label>Description</label>
        <input name="description" value="{{ old('description', $type->description) }}">
        <div style="margin-top:1.25rem"><button class="btn">{{ $type->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
