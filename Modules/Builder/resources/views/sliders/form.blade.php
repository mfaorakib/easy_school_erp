@extends('layouts.admin')
@section('title', 'Slider')

@section('content')
<div class="page-head">
    <h1>{{ $slider->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.sliders') }}</h1>
    <a href="{{ route('builder.sliders.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a>
</div>

<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $slider->exists ? route('builder.sliders.update', $slider) : route('builder.sliders.store') }}" enctype="multipart/form-data">
        @csrf @if($slider->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>Title</label><input name="title" value="{{ old('title', $slider->title) }}"></div>
            <div><label>Subtitle</label><input name="subtitle" value="{{ old('subtitle', $slider->subtitle) }}"></div>
            <div style="grid-column:1/-1">
                <label>{{ __('ui.image') }}</label>
                @if($slider->image_path)<div style="margin-bottom:.4rem"><img src="{{ \Modules\Builder\Services\BuilderService::media($slider->image_path) }}" style="height:64px;border-radius:8px"></div>@endif
                <input type="file" name="image" accept="image/*">
            </div>
            <div><label>Link URL</label><input name="link_url" value="{{ old('link_url', $slider->link_url) }}" placeholder="/p/admissions or https://…"></div>
            <div><label>Link Label</label><input name="link_label" value="{{ old('link_label', $slider->link_label) }}"></div>
            <div><label>Position</label><input type="number" name="position" min="0" value="{{ old('position', $slider->position ?? 0) }}"></div>
            <div style="align-self:end"><label style="display:flex;align-items:center;gap:.4rem;margin:0"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $slider->exists ? $slider->is_active : true) ? 'checked' : '' }}> Active</label></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $slider->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
