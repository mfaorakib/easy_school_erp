@extends('layouts.admin')
@section('title', 'ID Card Templates')

@section('content')
<div class="page-head"><h1>{{ __('ui.id_card_templates') }}</h1>
    <a href="{{ route('documents.idCardTemplates.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>

<div class="card" style="max-width:820px">
    <form method="POST"
          action="{{ $template->exists ? route('documents.idCardTemplates.update', $template) : route('documents.idCardTemplates.store') }}"
          enctype="multipart/form-data">
        @csrf @if($template->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.name') }} *</label>
                <input name="name" type="text" maxlength="255" value="{{ old('name', $template->name) }}" required>
                @error('name')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>

            <div><label>{{ __('ui.holder_type') }} *</label>
                <select name="holder_type" required>
                    <option value="student" {{ old('holder_type', $template->holder_type) === 'student' ? 'selected' : '' }}>{{ __('ui.students') }}</option>
                    <option value="staff" {{ old('holder_type', $template->holder_type) === 'staff' ? 'selected' : '' }}>{{ __('ui.staff') }}</option>
                </select>
                @error('holder_type')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>

            <div><label>{{ __('ui.heading') }}</label>
                <input name="title" type="text" maxlength="255" placeholder="Defaults to school name" value="{{ old('title', $template->title) }}">
                @error('title')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>

            <div><label>{{ __('ui.orientation') }}</label>
                <select name="orientation" required>
                    <option value="portrait" {{ old('orientation', $template->orientation) === 'portrait' ? 'selected' : '' }}>{{ __('ui.portrait') }}</option>
                    <option value="landscape" {{ old('orientation', $template->orientation) === 'landscape' ? 'selected' : '' }}>{{ __('ui.landscape') }}</option>
                </select>
                @error('orientation')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>

            <div><label>{{ __('ui.background_color') }}</label>
                <input type="color" name="background_color" value="{{ old('background_color', $template->background_color ?: '#4f46e5') }}">
                @error('background_color')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>

            <div><label>{{ __('ui.text_color') }}</label>
                <input type="color" name="text_color" value="{{ old('text_color', $template->text_color ?: '#ffffff') }}">
                @error('text_color')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>

            <div><label>Logo</label>
                <input type="file" name="logo" accept="image/*">
                @if($template->exists && $template->logo_path)
                    <div style="margin-top:.5rem"><img src="{{ \Modules\Documents\Services\DocumentService::media($template->logo_path) }}" alt="Logo" style="max-height:60px"></div>
                @endif
                @error('logo')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>

            <div><label>Background image</label>
                <input type="file" name="background_image" accept="image/*">
                @if($template->exists && $template->background_image_path)
                    <div style="margin-top:.5rem"><img src="{{ \Modules\Documents\Services\DocumentService::media($template->background_image_path) }}" alt="Background" style="max-height:60px"></div>
                @endif
                @error('background_image')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>

            <div><label>{{ __('ui.signature') }} image</label>
                <input type="file" name="signature" accept="image/*">
                @if($template->exists && $template->signature_path)
                    <div style="margin-top:.5rem"><img src="{{ \Modules\Documents\Services\DocumentService::media($template->signature_path) }}" alt="Signature" style="max-height:60px"></div>
                @endif
                @error('signature')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>

            <div><label>Signature label</label>
                <input name="signature_label" type="text" maxlength="100" value="{{ old('signature_label', $template->signature_label) }}">
                @error('signature_label')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>

            <div style="grid-column:1/-1"><label>Footer text</label>
                <input name="footer_text" type="text" maxlength="255" value="{{ old('footer_text', $template->footer_text) }}">
                @error('footer_text')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>

            <div style="grid-column:1/-1"><label>{{ __('ui.fields') }}</label>
                <div style="display:flex;flex-wrap:wrap;gap:.75rem">
                    @foreach(\Modules\Documents\Models\IdCardTemplate::AVAILABLE_FIELDS as $f)
                        <label style="display:inline-flex;align-items:center;gap:.35rem;font-weight:normal">
                            <input type="checkbox" name="fields[]" value="{{ $f }}" {{ in_array($f, old('fields', (array) $template->fields)) ? 'checked' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $f)) }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div style="grid-column:1/-1">
                <label style="display:inline-flex;align-items:center;gap:.35rem;font-weight:normal">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->exists ? $template->is_active : true) ? 'checked' : '' }}>
                    Active
                </label>
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $template->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
