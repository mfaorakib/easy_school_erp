@extends('layouts.admin')
@section('title', 'Page Builder')

@php
    use Modules\Builder\Support\BlockType;
    $viewUrl = $page->is_home ? url('/') : url('/p/'.$page->slug);
    $previewUrl = route('builder.pages.preview', $page);
    $openId = (int) request('block');
@endphp

@section('content')
<div class="page-head">
    <h1>{{ $page->title }} — {{ __('ui.builder') }}</h1>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        <button type="button" class="btn" id="addToggle">+ {{ __('ui.add_section') }}</button>
        <a href="{{ $viewUrl }}" target="_blank" class="btn btn-ghost">{{ __('ui.view_site') }} ↗</a>
        <a href="{{ route('builder.pages.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a>
    </div>
</div>

{{-- Add-section picker --}}
<div class="card picker" id="picker" hidden>
    <form method="POST" action="{{ route('builder.blocks.store', $page) }}">
        @csrf
        @foreach($types as $group => $items)
            <div class="pick-group">{{ $group }}</div>
            <div class="pick-row">
                @foreach($items as $key => $t)
                    <button class="pick" name="type" value="{{ $key }}" title="{{ $t['label'] }}">
                        <span class="pick-ico">{{ $t['icon'] }}</span>
                        <span>{{ $t['label'] }}</span>
                    </button>
                @endforeach
            </div>
        @endforeach
    </form>
</div>

<div class="builder-wrap">
    {{-- LEFT: section editor --}}
    <div class="builder-main">
        <div id="sectionList" data-reorder-url="{{ route('builder.blocks.reorder', $page) }}">
            @forelse($page->blocks as $block)
                <div class="sec-item {{ $block->id === $openId ? 'open' : '' }}" data-id="{{ $block->id }}" draggable="true">
                    <div class="sec-head-row">
                        <span class="drag" title="{{ __('ui.drag_to_reorder') }}">⠿</span>
                        <span class="sec-ico">{{ BlockType::icon($block->type) }}</span>
                        <strong class="sec-label">{{ BlockType::label($block->type) }}</strong>
                        @unless($block->is_visible)<span class="badge">{{ __('ui.hidden') }}</span>@endunless
                        <span class="sec-spacer"></span>
                        <form method="POST" action="{{ route('builder.blocks.duplicate', $block) }}" class="inline">
                            @csrf<button class="ic-btn" title="{{ __('ui.duplicate') }}">⧉</button>
                        </form>
                        <form method="POST" action="{{ route('builder.blocks.destroy', $block) }}" class="inline" onsubmit="return confirm('{{ __('ui.delete') }}?')">
                            @csrf @method('DELETE')<button class="ic-btn danger" title="{{ __('ui.delete') }}">🗑</button>
                        </form>
                        <button type="button" class="ic-btn caret" title="{{ __('ui.edit') }}">▾</button>
                    </div>

                    <div class="sec-body">
                        <form method="POST" action="{{ route('builder.blocks.update', $block) }}" class="sec-form" data-update>
                            @csrf
                            <input type="hidden" name="_method" value="PUT">

                            <div class="fld-grid">
                                @foreach(BlockType::fields($block->type) as $field)
                                    @include('builder::blocks._field', ['field' => $field, 'block' => $block])
                                @endforeach
                            </div>

                            <details class="style-box">
                                <summary>🎨 {{ __('ui.design_style') }}</summary>
                                @include('builder::blocks._style', ['block' => $block])
                            </details>

                            <div class="sec-foot">
                                <label class="tgl">
                                    <input type="checkbox" name="is_visible" value="1" @checked($block->is_visible)>
                                    <span>{{ __('ui.visible') }}</span>
                                </label>
                                <button class="btn" type="submit">{{ __('ui.save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="card"><div class="empty">{{ __('ui.no_sections') }}</div></div>
            @endforelse
        </div>
    </div>

    {{-- RIGHT: live preview --}}
    <aside class="builder-preview">
        <div class="prev-bar">
            <span>{{ __('ui.live_preview') }}</span>
            <span class="prev-tools">
                <button type="button" class="ic-btn" data-w="100%" title="Desktop">🖥</button>
                <button type="button" class="ic-btn" data-w="768px" title="Tablet">📱</button>
                <button type="button" class="ic-btn" data-w="390px" title="Phone">📲</button>
                <button type="button" class="ic-btn" id="prevReload" title="{{ __('ui.refresh') }}">↻</button>
            </span>
        </div>
        <div class="prev-shell"><iframe id="preview" src="{{ $previewUrl }}" title="preview"></iframe></div>
    </aside>
</div>

<style>
    /* All colors below use the admin layout's theme variables (--surface, --text, --muted,
       --border, --primary, --sidebar…) so this editor follows light/dark mode correctly —
       hardcoding hex here previously caused unreadable text under prefers-color-scheme:dark. */
    .picker{max-height:340px;overflow:auto}
    .pick-group{font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);font-weight:700;margin:.6rem 0 .4rem}
    .pick-row{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:.5rem;margin-bottom:.4rem}
    .pick{display:flex;align-items:center;gap:.5rem;padding:.6rem .7rem;border:1px solid var(--border);border-radius:10px;background:var(--surface);color:var(--text);cursor:pointer;font:inherit;text-align:start}
    .pick:hover{border-color:var(--primary);background:rgba(79,70,229,.10)}
    .pick-ico{font-size:1.15rem}

    .builder-wrap{display:grid;grid-template-columns:1fr 460px;gap:20px;align-items:start}
    .builder-preview{position:sticky;top:14px}
    .prev-bar{display:flex;justify-content:space-between;align-items:center;background:var(--sidebar);color:var(--sidebar-text);padding:.5rem .8rem;border-radius:12px 12px 0 0;font-size:.85rem;font-weight:600}
    .prev-tools{display:flex;gap:.2rem}
    .prev-tools .ic-btn{color:var(--sidebar-text)}
    .prev-tools .ic-btn:hover{background:rgba(255,255,255,.12)}
    .prev-shell{border:1px solid var(--border);border-top:0;border-radius:0 0 12px 12px;overflow:hidden;background:var(--surface);display:flex;justify-content:center}
    #preview{width:100%;height:calc(100vh - 150px);min-height:520px;border:0;transition:width .2s;background:#fff}

    .sec-item{background:var(--surface);border:1px solid var(--border);border-radius:14px;margin-bottom:.7rem;box-shadow:0 1px 2px rgba(0,0,0,.04)}
    .sec-item.dragging{opacity:.5}
    .sec-head-row{display:flex;align-items:center;gap:.55rem;padding:.7rem .9rem;color:var(--text)}
    .drag{cursor:grab;color:var(--muted);font-size:1.1rem}
    .sec-ico{font-size:1.1rem;cursor:pointer}
    .sec-label{font-size:.98rem;cursor:pointer}
    .sec-spacer{flex:1}
    .ic-btn{background:none;border:0;cursor:pointer;font-size:1rem;padding:.25rem .4rem;border-radius:8px;color:var(--muted)}
    .ic-btn:hover{background:var(--surface-2)}
    .ic-btn.danger:hover{background:rgba(220,38,38,.12)}
    .inline{display:inline}
    .caret{transition:transform .2s}
    .sec-item.open .caret{transform:rotate(180deg)}
    .sec-body{display:none;padding:0 .9rem .9rem;border-top:1px solid var(--border)}
    .sec-item.open .sec-body{display:block}

    .fld-grid,.style-grid{display:grid;grid-template-columns:1fr 1fr;gap:.7rem .8rem;margin-top:.8rem}
    .fld-wide,.fld-rep{grid-column:1/-1}
    .fld-lbl{display:block;font-size:.8rem;font-weight:600;color:var(--muted);margin-bottom:.25rem}
    .fld input,.fld select,.fld textarea,.style-grid input,.style-grid select{width:100%;padding:.5rem .6rem;border:1px solid var(--border);border-radius:9px;font:inherit;background:var(--surface);color:var(--text)}
    .fld input[type=color]{padding:.2rem;height:38px}
    .fld input[type=range]{padding:0}
    .tgl{display:inline-flex;align-items:center;gap:.4rem;font-size:.85rem;font-weight:600;color:var(--muted);cursor:pointer}
    .tgl input{width:auto}
    .rep-rows{display:flex;flex-direction:column;gap:.5rem}
    .rep-row{display:flex;align-items:flex-start;gap:.6rem;padding:.6rem;border:1px dashed var(--border);border-radius:10px;background:var(--surface-2)}
    .rep-no{flex:0 0 auto;width:22px;height:22px;border-radius:50%;background:var(--border);color:var(--text);font-size:.72rem;font-weight:700;display:flex;align-items:center;justify-content:center;margin-top:.2rem}
    .rep-row-fields{flex:1;min-width:0;display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:.5rem}
    .rep-hint{font-size:.72rem;color:var(--muted);margin:.3rem 0 0}
    .fld-rep-lbl{font-size:.85rem;color:var(--text)}
    .style-box{margin-top:.9rem;border:1px solid var(--border);border-radius:10px;background:var(--surface-2);padding:.2rem .7rem}
    .style-box summary{cursor:pointer;font-weight:600;font-size:.85rem;color:var(--muted);padding:.5rem 0}
    .sec-foot{display:flex;justify-content:space-between;align-items:center;margin-top:1rem}

    .color-toggle{display:flex;align-items:center;gap:.5rem}
    .tgl-sm{display:flex;align-items:center;gap:.3rem;font-size:.78rem;color:var(--muted);white-space:nowrap}
    .tgl-sm input{width:auto}
    .color-toggle input[type=color]{flex:1;min-width:0}
    .color-toggle input[type=color]:disabled{opacity:.4}

    /* Rich-text (WYSIWYG) field */
    .rte{border:1px solid var(--border);border-radius:9px;overflow:hidden;background:var(--surface)}
    .rte-toolbar{display:flex;flex-wrap:wrap;gap:.15rem;padding:.35rem;background:var(--surface-2);border-bottom:1px solid var(--border)}
    .rte-toolbar button{background:none;border:0;min-height:28px;border-radius:6px;cursor:pointer;font-size:.78rem;color:var(--text);padding:0 .4rem}
    .rte-toolbar button:hover{background:var(--border)}
    .rte-sep{width:1px;background:var(--border);margin:2px 4px}
    .rte-editor{min-height:130px;max-height:360px;overflow:auto;padding:.6rem .7rem;font:inherit;line-height:1.55;color:var(--text)}
    .rte-editor:focus{outline:none}
    .rte-editor:empty:before{content:attr(data-placeholder);color:var(--muted)}
    .rte-editor p{margin:0 0 .6em}
    .rte-editor h2,.rte-editor h3{margin:.4em 0}
    .rte-editor blockquote{margin:.5em 0;padding-left:.8em;border-left:3px solid var(--border);color:var(--muted)}
    .rte-editor ul,.rte-editor ol{margin:.4em 0;padding-left:1.4em}
    .rte-editor img{max-width:100%;border-radius:8px;display:block;margin:.4em 0}

    @media(max-width:1100px){.builder-wrap{grid-template-columns:1fr}.builder-preview{position:static}#preview{height:70vh}}
</style>

<script>
(function () {
    var token = '{{ csrf_token() }}';
    var iframe = document.getElementById('preview');
    function reloadPreview() { try { iframe.contentWindow.location.reload(); } catch (e) { iframe.src = iframe.src; } }

    var addBtn = document.getElementById('addToggle'), picker = document.getElementById('picker');
    if (addBtn) addBtn.addEventListener('click', function () { picker.hidden = !picker.hidden; });

    document.querySelectorAll('.sec-item .caret, .sec-item .sec-label, .sec-item .sec-ico').forEach(function (el) {
        el.addEventListener('click', function () { el.closest('.sec-item').classList.toggle('open'); });
    });

    document.querySelectorAll('.prev-tools [data-w]').forEach(function (b) {
        b.addEventListener('click', function () { iframe.style.width = b.getAttribute('data-w'); });
    });
    var rl = document.getElementById('prevReload');
    if (rl) rl.addEventListener('click', reloadPreview);

    // Repeater ↔ sibling-select sync: e.g. Columns' "Column Content" rows follow
    // "Number of columns" — picking a higher count reveals that many content slots live.
    document.querySelectorAll('[data-sync-with]').forEach(function (rep) {
        var form = rep.closest('form');
        var select = form && form.querySelector('[name="' + rep.getAttribute('data-sync-with') + '"]');
        var template = rep.querySelector('.rep-template');
        var rowsBox = rep.querySelector('.rep-rows');
        if (!select || !template || !rowsBox) return;

        select.addEventListener('change', function () {
            var target = parseInt(select.value, 10) || 2;
            var have = rowsBox.querySelectorAll(':scope > .rep-row').length;
            for (var i = have; i < target; i++) {
                var clone = template.content.cloneNode(true);
                clone.querySelectorAll('[name]').forEach(function (el) {
                    el.name = el.name.replace('__INDEX__', i);
                });
                var no = clone.querySelector('.rep-no');
                if (no) no.textContent = i + 1;
                rowsBox.appendChild(clone);
            }
        });
    });

    // Text-color toggle: enable/disable the paired color input so unchecked = "auto" (not submitted)
    document.querySelectorAll('.js-color-on').forEach(function (chk) {
        var target = document.getElementById(chk.getAttribute('data-target'));
        if (!target) return;
        target.disabled = !chk.checked;
        chk.addEventListener('change', function () { target.disabled = !chk.checked; });
    });

    // Rich-text (WYSIWYG) fields — vanilla contenteditable + execCommand, no library
    function syncRte(rte) {
        var editor = rte.querySelector('.rte-editor'), hidden = rte.querySelector('.rte-hidden');
        if (editor && hidden) hidden.value = editor.innerHTML;
    }
    function normalizeUrl(u) {
        u = (u || '').trim();
        if (!u) return null;
        if (/^https?:\/\//i.test(u) || u.charAt(0) === '/') return u;
        return '/storage/' + u.replace(/^\/+/, '');
    }
    document.addEventListener('input', function (e) {
        var editor = e.target.closest && e.target.closest('.rte-editor');
        if (editor) syncRte(editor.closest('.rte'));
    });
    document.addEventListener('click', function (e) {
        var btn = e.target.closest && e.target.closest('.rte-toolbar button');
        if (!btn) return;
        e.preventDefault();
        var rte = btn.closest('.rte'), editor = rte.querySelector('.rte-editor');
        editor.focus();
        var cmd = btn.getAttribute('data-cmd'), val = btn.getAttribute('data-val') || null;
        if (cmd === 'createLink') {
            val = normalizeUrl(window.prompt('Link URL:', 'https://'));
            if (!val) return;
        } else if (cmd === 'insertImage') {
            val = normalizeUrl(window.prompt('Image URL or storage path:', ''));
            if (!val) return;
        }
        document.execCommand(cmd, false, val);
        syncRte(rte);
    });

    // AJAX save → refresh preview
    document.querySelectorAll('form[data-update]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = form.querySelector('button[type=submit]'); var old = btn.textContent;
            btn.textContent = '…';
            fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function () { btn.textContent = '✓'; reloadPreview(); setTimeout(function () { btn.textContent = old; }, 1200); })
                .catch(function () { btn.textContent = old; form.submit(); });
        });
    });

    // Drag-to-reorder
    var list = document.getElementById('sectionList');
    if (list) {
        var dragging = null;
        list.querySelectorAll('.sec-item').forEach(function (item) {
            item.addEventListener('dragstart', function () { dragging = item; item.classList.add('dragging'); });
            item.addEventListener('dragend', function () {
                item.classList.remove('dragging'); dragging = null;
                var order = Array.prototype.map.call(list.querySelectorAll('.sec-item'), function (x) { return x.getAttribute('data-id'); });
                var fd = new FormData(); fd.append('_token', token);
                order.forEach(function (id) { fd.append('order[]', id); });
                fetch(list.getAttribute('data-reorder-url'), { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(reloadPreview);
            });
        });
        list.addEventListener('dragover', function (e) {
            e.preventDefault();
            if (!dragging) return;
            var after = null;
            list.querySelectorAll('.sec-item:not(.dragging)').forEach(function (x) {
                var box = x.getBoundingClientRect();
                if (e.clientY > box.top + box.height / 2) after = x;
            });
            if (after == null) list.insertBefore(dragging, list.querySelector('.sec-item:not(.dragging)'));
            else after.parentNode.insertBefore(dragging, after.nextSibling);
        });
    }
})();
</script>
@endsection
