@php
    /** @var \Modules\Builder\Models\CmsBlock $block */
    $s = $block->settings ?? [];
    $sel = fn ($k, $v, $d = null) => (string) ($s[$k] ?? $d) === (string) $v ? 'selected' : '';
@endphp
<div class="style-grid">
    <div class="fld">
        <label class="fld-lbl">{{ __('ui.background') }}</label>
        <select name="settings[bg_type]" class="js-bgtype">
            <option value="none" {{ $sel('bg_type', 'none', 'none') }}>{{ __('ui.bg_none') }}</option>
            <option value="color" {{ $sel('bg_type', 'color') }}>{{ __('ui.bg_color') }}</option>
            <option value="gradient" {{ $sel('bg_type', 'gradient') }}>{{ __('ui.bg_gradient') }}</option>
            <option value="image" {{ $sel('bg_type', 'image') }}>{{ __('ui.bg_image') }}</option>
        </select>
    </div>
    <div class="fld">
        <label class="fld-lbl">{{ __('ui.color') }} 1</label>
        <input type="color" name="settings[bg_color]" value="{{ $s['bg_color'] ?? '#4f46e5' }}">
    </div>
    <div class="fld">
        <label class="fld-lbl">{{ __('ui.color') }} 2</label>
        <input type="color" name="settings[bg_color2]" value="{{ $s['bg_color2'] ?? '#0ea5e9' }}">
    </div>
    <div class="fld fld-wide">
        <label class="fld-lbl">{{ __('ui.bg_image') }} (URL / path)</label>
        <input name="settings[bg_image]" value="{{ $s['bg_image'] ?? '' }}" placeholder="Image URL or storage path">
    </div>
    <div class="fld">
        <label class="fld-lbl">{{ __('ui.overlay') }}</label>
        <input type="range" name="settings[overlay]" min="0" max="0.8" step="0.05" value="{{ $s['overlay'] ?? 0.45 }}">
    </div>
    <div class="fld">
        <label class="fld-lbl">{{ __('ui.text_theme') }}</label>
        <select name="settings[text_theme]">
            <option value="auto" {{ $sel('text_theme', 'auto', 'auto') }}>{{ __('ui.auto') }}</option>
            <option value="light" {{ $sel('text_theme', 'light') }}>{{ __('ui.light') }}</option>
            <option value="dark" {{ $sel('text_theme', 'dark') }}>{{ __('ui.dark') }}</option>
        </select>
    </div>
    <div class="fld">
        <label class="fld-lbl">{{ __('ui.text_color') }}</label>
        <div class="color-toggle">
            <label class="tgl-sm">
                <input type="checkbox" class="js-color-on" data-target="tc-{{ $block->id }}" @checked(! empty($s['text_color']))>
                {{ __('ui.custom') }}
            </label>
            <input type="color" name="settings[text_color]" id="tc-{{ $block->id }}"
                   value="{{ $s['text_color'] ?? '#111827' }}" {{ empty($s['text_color']) ? 'disabled' : '' }}>
        </div>
    </div>
    <div class="fld">
        <label class="fld-lbl">{{ __('ui.padding') }}</label>
        <select name="settings[pad_y]">
            @foreach(['none' => 'None', 'sm' => 'S', 'md' => 'M', 'lg' => 'L', 'xl' => 'XL'] as $pv => $pl)
                <option value="{{ $pv }}" {{ $sel('pad_y', $pv, 'lg') }}>{{ $pl }}</option>
            @endforeach
        </select>
    </div>
    <div class="fld">
        <label class="fld-lbl">{{ __('ui.width') }}</label>
        <select name="settings[width]">
            <option value="boxed" {{ $sel('width', 'boxed', 'boxed') }}>{{ __('ui.boxed') }}</option>
            <option value="narrow" {{ $sel('width', 'narrow') }}>{{ __('ui.narrow') }}</option>
            <option value="full" {{ $sel('width', 'full') }}>{{ __('ui.full_width') }}</option>
        </select>
    </div>
    <div class="fld">
        <label class="fld-lbl">{{ __('ui.alignment') }}</label>
        <select name="settings[align]">
            <option value="left" {{ $sel('align', 'left', 'left') }}>{{ __('ui.left') }}</option>
            <option value="center" {{ $sel('align', 'center') }}>{{ __('ui.center') }}</option>
            <option value="right" {{ $sel('align', 'right') }}>{{ __('ui.right') }}</option>
        </select>
    </div>
    <div class="fld">
        <label class="fld-lbl">{{ __('ui.anchor_id') }}</label>
        <input name="settings[anchor]" value="{{ $s['anchor'] ?? '' }}" placeholder="section-id">
    </div>
    <label class="tgl">
        <input type="checkbox" name="settings[soft]" value="1" @checked(! empty($s['soft']))>
        <span>{{ __('ui.soft_background') }}</span>
    </label>
</div>
