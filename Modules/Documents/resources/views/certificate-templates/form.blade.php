@extends('layouts.admin')
@section('title', 'Certificate Templates')

@section('content')
<div class="page-head"><h1>{{ $template->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.certificate_templates') }}</h1>
    <a href="{{ route('documents.certificateTemplates.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>

<div style="display:flex;gap:1.25rem;align-items:flex-start;flex-wrap:wrap">
<div class="card" style="flex:1 1 480px;max-width:820px">
    <form method="POST" enctype="multipart/form-data"
          action="{{ $template->exists ? route('documents.certificateTemplates.update', $template) : route('documents.certificateTemplates.store') }}">
        @csrf @if($template->exists) @method('PUT') @endif
        <input type="hidden" name="template_id" value="{{ $template->id }}">
        <div class="grid">
            <div><label>{{ __('ui.name') }} *</label>
                <input name="name" type="text" maxlength="255" value="{{ old('name', $template->name) }}" required>
                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div><label>{{ __('ui.holder_type') }} *</label>
                <select name="holder_type" required>
                    <option value="student" {{ old('holder_type', $template->holder_type) === 'student' ? 'selected' : '' }}>{{ __('ui.students') }}</option>
                    <option value="staff" {{ old('holder_type', $template->holder_type) === 'staff' ? 'selected' : '' }}>{{ __('ui.staff') }}</option>
                    <option value="general" {{ old('holder_type', $template->holder_type) === 'general' ? 'selected' : '' }}>{{ __('ui.general') }}</option>
                </select>
                @error('holder_type')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div><label>Document Number Prefix (optional)</label>
                <input name="document_prefix" type="text" maxlength="20" value="{{ old('document_prefix', $template->document_prefix) }}" placeholder="e.g. TC, EXP">
                <small>If set (e.g. TC, EXP), this certificate gets an auto-numbered {{ '{'.'{'.'document_no'.'}'.'}' }} the first time it's generated for each person, reused on every reprint. Leave blank if this certificate doesn't need a formal reference number.</small>
                @error('document_prefix')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div><label>{{ __('ui.heading') }}</label>
                <input name="heading" type="text" maxlength="255" value="{{ old('heading', $template->heading) }}">
                @error('heading')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div><label>{{ __('ui.orientation') }}</label>
                <select name="orientation" required>
                    <option value="portrait" {{ old('orientation', $template->orientation) === 'portrait' ? 'selected' : '' }}>{{ __('ui.portrait') }}</option>
                    <option value="landscape" {{ old('orientation', $template->orientation) === 'landscape' ? 'selected' : '' }}>{{ __('ui.landscape') }}</option>
                </select>
                @error('orientation')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div><label>Accent color</label>
                <input name="accent_color" type="color" value="{{ old('accent_color', $template->accent_color ?: '#b9942f') }}" style="height:2.4rem;padding:.2rem">
                <small>Used for the border, heading, and divider line.</small>
                @error('accent_color')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div><label>Font style</label>
                <select name="font_family">
                    <option value="serif" {{ old('font_family', $template->font_family ?: 'serif') === 'serif' ? 'selected' : '' }}>Classic serif</option>
                    <option value="elegant" {{ old('font_family', $template->font_family) === 'elegant' ? 'selected' : '' }}>Elegant serif</option>
                    <option value="sans" {{ old('font_family', $template->font_family) === 'sans' ? 'selected' : '' }}>Modern sans-serif</option>
                </select>
                @error('font_family')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div><label>Border style</label>
                <select name="border_style">
                    <option value="classic" {{ old('border_style', $template->border_style ?: 'classic') === 'classic' ? 'selected' : '' }}>Classic double frame</option>
                    <option value="simple" {{ old('border_style', $template->border_style) === 'simple' ? 'selected' : '' }}>Simple single line</option>
                    <option value="none" {{ old('border_style', $template->border_style) === 'none' ? 'selected' : '' }}>No border</option>
                </select>
                @error('border_style')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div style="grid-column:1/-1"><label>{{ __('ui.body') }} *</label>
                <textarea name="body" rows="6">{{ old('body', $template->body) }}</textarea>
                @error('body')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div style="grid-column:1/-1" class="card">
                <strong>{{ __('ui.placeholders') }}</strong>
                <div style="margin-top:.5rem">
                    @foreach($placeholders as $p)
                        <span class="badge" style="margin:.15rem">{{ '{'.'{'.$p['key'].'}'.'}' }} — {{ $p['label'] }}</span>
                    @endforeach
                </div>
            </div>

            <div><label>Header image</label>
                <input name="header_image" type="file" accept="image/*">
                @if($template->exists && $template->header_image_path)
                    <div style="margin-top:.5rem"><img src="{{ \Modules\Documents\Services\DocumentService::media($template->header_image_path) }}" alt="Header image" style="max-width:180px;max-height:90px"></div>
                @endif
                @error('header_image')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div><label>Background image</label>
                <input name="background_image" type="file" accept="image/*">
                @if($template->exists && $template->background_image_path)
                    <div style="margin-top:.5rem"><img src="{{ \Modules\Documents\Services\DocumentService::media($template->background_image_path) }}" alt="Background image" style="max-width:180px;max-height:90px"></div>
                @endif
                @error('background_image')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div><label>Left {{ __('ui.signature') }} image</label>
                <input name="signature_left" type="file" accept="image/*">
                @if($template->exists && $template->signature_left_path)
                    <div style="margin-top:.5rem"><img src="{{ \Modules\Documents\Services\DocumentService::media($template->signature_left_path) }}" alt="Left signature" style="max-width:180px;max-height:90px"></div>
                @endif
                @error('signature_left')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div><label>Left {{ __('ui.signature') }} label</label>
                <input name="signature_left_label" type="text" maxlength="100" value="{{ old('signature_left_label', $template->signature_left_label) }}">
                @error('signature_left_label')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div><label>Right {{ __('ui.signature') }} image</label>
                <input name="signature_right" type="file" accept="image/*">
                @if($template->exists && $template->signature_right_path)
                    <div style="margin-top:.5rem"><img src="{{ \Modules\Documents\Services\DocumentService::media($template->signature_right_path) }}" alt="Right signature" style="max-width:180px;max-height:90px"></div>
                @endif
                @error('signature_right')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div><label>Right {{ __('ui.signature') }} label</label>
                <input name="signature_right_label" type="text" maxlength="100" value="{{ old('signature_right_label', $template->signature_right_label) }}">
                @error('signature_right_label')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div style="grid-column:1/-1">
                <label><input name="is_active" type="checkbox" value="1" {{ old('is_active', $template->exists ? $template->is_active : true) ? 'checked' : '' }}> Active</label>
            </div>
        </div>
        <div style="margin-top:1.25rem;display:flex;gap:.6rem;flex-wrap:wrap">
            <button class="btn">{{ $template->exists ? __('ui.update') : __('ui.create') }}</button>
            <button type="submit" class="btn btn-ghost" formaction="{{ route('documents.certificateTemplates.preview') }}" formtarget="certPreviewFrame" formnovalidate>
                <i class="fa-solid fa-eye" aria-hidden="true"></i> Preview
            </button>
            <button type="submit" class="btn btn-ghost" formaction="{{ route('documents.certificateTemplates.preview') }}" formtarget="_blank" formnovalidate>
                <i class="fa-solid fa-up-right-from-square" aria-hidden="true"></i> Open in New Tab
            </button>
        </div>
    </form>
</div>

<div class="card" style="flex:1 1 360px;min-width:320px;position:sticky;top:1rem;padding:0;overflow:hidden">
    <div style="padding:.8rem 1rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap">
        <strong><i class="fa-solid fa-eye" aria-hidden="true"></i> {{ __('ui.preview') }}</strong>
        <small style="color:var(--muted)">Click "Preview" to refresh</small>
    </div>
    <iframe name="certPreviewFrame" src="about:blank" title="Certificate preview" style="width:100%;height:70vh;border:0;display:block;background:#e5e7eb"></iframe>
</div>
</div>
@endsection
