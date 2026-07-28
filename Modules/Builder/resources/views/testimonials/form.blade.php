@extends('layouts.admin')
@section('title', 'Testimonial')

@section('content')
<div class="page-head">
    <h1>{{ $testimonial->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.testimonials') }}</h1>
    <a href="{{ route('builder.testimonials.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a>
</div>

<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $testimonial->exists ? route('builder.testimonials.update', $testimonial) : route('builder.testimonials.store') }}" enctype="multipart/form-data">
        @csrf @if($testimonial->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.name') }} *</label><input name="name" value="{{ old('name', $testimonial->name) }}" required></div>
            <div><label>Designation</label><input name="designation" value="{{ old('designation', $testimonial->designation) }}"></div>
            <div><label>Organization</label><input name="organization" value="{{ old('organization', $testimonial->organization) }}"></div>
            <div>
                <label>Photo</label>
                @if($testimonial->photo_path)<div style="margin-bottom:.4rem"><img src="{{ \Modules\Builder\Services\BuilderService::media($testimonial->photo_path) }}" style="width:48px;height:48px;border-radius:50%;object-fit:cover"></div>@endif
                <input type="file" name="photo" accept="image/*">
            </div>
            <div style="grid-column:1/-1"><label>Quote *</label><textarea name="quote" rows="3" required>{{ old('quote', $testimonial->quote) }}</textarea></div>
            <div><label>{{ __('ui.rating') }}</label>
                <select name="rating">
                    @for($r = 5; $r >= 1; $r--)<option value="{{ $r }}" {{ (int) old('rating', $testimonial->rating ?? 5) === $r ? 'selected' : '' }}>{{ str_repeat('★', $r) }} ({{ $r }})</option>@endfor
                </select>
            </div>
            <div><label>Position</label><input type="number" name="position" min="0" value="{{ old('position', $testimonial->position ?? 0) }}"></div>
            <div style="align-self:end"><label style="display:flex;align-items:center;gap:.4rem;margin:0"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $testimonial->exists ? $testimonial->is_active : true) ? 'checked' : '' }}> Active</label></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $testimonial->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
