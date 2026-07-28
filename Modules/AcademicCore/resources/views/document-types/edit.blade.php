@extends('layouts.admin')
@section('title', 'Edit Document Type')

@section('content')
<div class="page-head"><h1>{{ __('ui.edit') }} — {{ __('ui.document_types') }}</h1>
    <a href="{{ route('academic.document-types.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>

<div class="card max-w-full" style="max-width:680px">
    <form method="POST" action="{{ route('academic.document-types.update', $documentType) }}">
        @csrf
        @method('PUT')
        <div class="grid">
            <div><label>{{ __('ui.name') }} *</label>
                <input name="name" type="text" maxlength="120" value="{{ old('name', $documentType->name) }}" required>
                @error('name')<div class="badge btn-danger">{{ $message }}</div>@enderror
            </div>
            <div><label>Sort Order</label>
                <input name="sort_order" type="number" min="0" value="{{ old('sort_order', $documentType->sort_order ?? 0) }}">
                @error('sort_order')<div class="badge btn-danger">{{ $message }}</div>@enderror
            </div>
            <div style="grid-column:1/-1">
                <label><input name="is_required" type="checkbox" value="1" {{ old('is_required', $documentType->is_required) ? 'checked' : '' }}> {{ __('ui.required') }}</label>
            </div>
            <div style="grid-column:1/-1">
                <label><input name="is_active" type="checkbox" value="1" {{ old('is_active', $documentType->is_active) ? 'checked' : '' }}> {{ __('ui.active') }}</label>
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ __('ui.update') }}</button></div>
    </form>
</div>
@endsection
