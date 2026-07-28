@extends('layouts.admin')
@section('title', __('ui.sidebar_manager'))

@section('content')
<div class="page-head"><h1>{{ __('ui.sidebar_manager') }}</h1></div>

<p class="sb-hint">Drag a sub-menu into a different section to move it there — this saves immediately. Reordering within one section still needs its own Save button.</p>

<div class="card sb-card">
    <div class="sb-card-head">
        <h2>Add Custom Section</h2>
    </div>
    <form method="POST" action="{{ route('settings.sidebarManager.sections.store') }}">
        @csrf
        <div class="grid">
            <div>
                <label>{{ __('ui.label') }}</label>
                <input type="text" name="label" maxlength="100" required value="{{ old('label') }}">
                @error('label')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>Icon</label>
                <input type="text" name="icon" maxlength="8" placeholder="📁" value="{{ old('icon') }}">
            </div>
        </div>
        <div class="sb-actions"><button class="btn">{{ __('ui.add') }}</button></div>
    </form>
</div>

<div class="card sb-card">
    <div class="sb-card-head">
        <h2>{{ __('ui.top_level_sections') }}</h2>
        <form method="POST" action="{{ route('settings.sidebarManager.reset') }}" class="inline">
            @csrf
            <input type="hidden" name="scope" value="">
            <button class="btn btn-sm btn-ghost">{{ __('ui.reset_to_default') }}</button>
        </form>
    </div>

    <form method="POST" action="{{ route('settings.sidebarManager.sections.update') }}">
        @csrf
        <ul class="sb-list" data-sb-list data-kind="section">
            @foreach($sections as $section)
                <li class="sb-item" draggable="true" data-kind="section">
                    <span class="sb-drag" title="{{ __('ui.drag_to_reorder') }}"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></span>
                    <span class="sb-ico">{{ $section['icon'] }}</span>
                    <span class="sb-label">{{ $section['label'] }}</span>
                    <input type="hidden" name="order[]" value="{{ $section['key'] }}">
                    <button type="submit" form="sb-hide-section-{{ $section['key'] }}" class="btn btn-sm btn-ghost sb-hide-btn{{ in_array($section['key'], $hiddenKeys) ? ' is-hidden' : '' }}" title="{{ in_array($section['key'], $hiddenKeys) ? 'Hidden from Admin — click to show' : 'Visible to Admin — click to hide' }}">
                        @if(in_array($section['key'], $hiddenKeys))
                            <i class="fa-solid fa-eye-slash" aria-hidden="true"></i> Hidden
                        @else
                            <i class="fa-solid fa-eye" aria-hidden="true"></i> Visible
                        @endif
                    </button>
                    @if($section['is_custom'])
                        <button type="submit" form="sb-delete-{{ $section['key'] }}" class="btn btn-sm btn-ghost sb-delete-btn" title="{{ __('ui.delete') }}"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                    @endif
                </li>
            @endforeach
        </ul>
        <div class="sb-actions"><button class="btn">{{ __('ui.save_order') }}</button></div>
    </form>

    {{-- Hidden delete/hide-toggle forms live outside the reorder form above — a <form> can't nest inside another <form>. Each row's hide button (rendered inline above) points at its form here via the HTML `form="id"` attribute. --}}
    @foreach($sections as $section)
        @if($section['is_custom'])
            <form id="sb-delete-{{ $section['key'] }}" method="POST" action="{{ route('settings.sidebarManager.sections.destroy', $section['key']) }}" class="sb-hidden-form" onsubmit="return confirm('Delete this section? Any items in it will move back to their default section.')">
                @csrf
                @method('DELETE')
            </form>
        @endif
        <form id="sb-hide-section-{{ $section['key'] }}" method="POST" action="{{ route('settings.sidebarManager.hidden.toggle') }}" class="sb-hidden-form">
            @csrf
            <input type="hidden" name="item_key" value="{{ $section['key'] }}">
        </form>
    @endforeach
</div>

@foreach($sections as $section)
    <div class="card sb-card">
        <div class="sb-card-head">
            <h2><span class="sb-ico">{{ $section['icon'] }}</span> {{ $section['label'] }}</h2>
            <form method="POST" action="{{ route('settings.sidebarManager.reset') }}" class="inline">
                @csrf
                <input type="hidden" name="scope" value="{{ $section['key'] }}">
                <button class="btn btn-sm btn-ghost">{{ __('ui.reset_to_default') }}</button>
            </form>
        </div>

        <form method="POST" action="{{ route('settings.sidebarManager.groups.update') }}">
            @csrf
            <input type="hidden" name="section_key" value="{{ $section['key'] }}">
            <ul class="sb-list sb-group-list" data-sb-list data-sb-group-list data-kind="group" data-section-key="{{ $section['key'] }}">
                @foreach($section['orderedGroups'] as $groupKey)
                    <li class="sb-item" draggable="true" data-kind="group" data-section-key="{{ $section['key'] }}" data-item-key="{{ $groupKey }}">
                        <span class="sb-drag" title="{{ __('ui.drag_to_reorder') }}"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></span>
                        <span class="sb-label">{{ $groupLabels[$groupKey]['label'] ?? $groupKey }}</span>
                        <input type="hidden" name="order[]" value="{{ $groupKey }}">
                        <button type="submit" form="sb-hide-group-{{ $groupKey }}" class="btn btn-sm btn-ghost sb-hide-btn{{ in_array($groupKey, $hiddenKeys) ? ' is-hidden' : '' }}" title="{{ in_array($groupKey, $hiddenKeys) ? 'Hidden from Admin — click to show' : 'Visible to Admin — click to hide' }}">
                            @if(in_array($groupKey, $hiddenKeys))
                                <i class="fa-solid fa-eye-slash" aria-hidden="true"></i> Hidden
                            @else
                                <i class="fa-solid fa-eye" aria-hidden="true"></i> Visible
                            @endif
                        </button>
                    </li>
                @endforeach
            </ul>
            <div class="sb-actions"><button class="btn">{{ __('ui.save_order') }}</button></div>
        </form>

        {{-- Hide-toggle forms for each sub-group — can't nest inside the reorder form above; their buttons (rendered inline above) target these via form="id". --}}
        @foreach($section['orderedGroups'] as $groupKey)
            <form id="sb-hide-group-{{ $groupKey }}" method="POST" action="{{ route('settings.sidebarManager.hidden.toggle') }}" class="sb-hidden-form">
                @csrf
                <input type="hidden" name="item_key" value="{{ $groupKey }}">
            </form>
        @endforeach

        {{-- Per-group link ordering: each sub-group owns its links' order via its own form + draggable list, scoped so links can't be dragged into a different group's list. --}}
        <div class="sb-links-wrap">
            @foreach($section['orderedGroups'] as $groupKey)
                <div class="sb-links-block">
                    <div class="sb-links-head">
                        <span>{{ $groupLabels[$groupKey]['label'] ?? $groupKey }}</span>
                        <span class="sb-links-sub">links</span>
                    </div>

                    <form method="POST" action="{{ route('settings.sidebarManager.links.update') }}">
                        @csrf
                        <input type="hidden" name="group_key" value="{{ $groupKey }}">
                        <ul class="sb-list sb-link-list" data-sb-list data-kind="link" data-group-key="{{ $groupKey }}">
                            @foreach($orderedLinksByGroup[$groupKey] ?? [] as $link)
                                @php([$linkSlug, $linkLabel] = $link)
                                <li class="sb-item sb-item-sm" draggable="true" data-kind="link" data-group-key="{{ $groupKey }}" data-item-key="{{ $linkSlug }}">
                                    <span class="sb-drag" title="{{ __('ui.drag_to_reorder') }}"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></span>
                                    <span class="sb-label">{{ $linkLabel }}</span>
                                    <input type="hidden" name="order[]" value="{{ $linkSlug }}">
                                    <button type="submit" form="sb-hide-link-{{ $groupKey }}-{{ $linkSlug }}" class="btn btn-sm btn-ghost sb-hide-btn{{ in_array($linkSlug, $hiddenKeys) ? ' is-hidden' : '' }}" title="{{ in_array($linkSlug, $hiddenKeys) ? 'Hidden from Admin — click to show' : 'Visible to Admin — click to hide' }}">
                                        @if(in_array($linkSlug, $hiddenKeys))
                                            <i class="fa-solid fa-eye-slash" aria-hidden="true"></i> Hidden
                                        @else
                                            <i class="fa-solid fa-eye" aria-hidden="true"></i> Visible
                                        @endif
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                        @if(empty($orderedLinksByGroup[$groupKey]))
                            <p class="sb-links-empty">No links in this sub-group.</p>
                        @endif
                        <div class="sb-actions"><button class="btn btn-sm">{{ __('ui.save_order') }}</button></div>
                    </form>

                    {{-- Hide-toggle forms for each link — can't nest inside the link-order form above; their buttons (rendered inline above) target these via form="id". --}}
                    @foreach($orderedLinksByGroup[$groupKey] ?? [] as $link)
                        @php([$linkSlug] = $link)
                        <form id="sb-hide-link-{{ $groupKey }}-{{ $linkSlug }}" method="POST" action="{{ route('settings.sidebarManager.hidden.toggle') }}" class="sb-hidden-form">
                            @csrf
                            <input type="hidden" name="item_key" value="{{ $linkSlug }}">
                        </form>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
@endforeach

{{-- Shared hidden form used by the cross-section drag-and-drop below: filled in and auto-submitted on drop. --}}
<form id="sb-assign-form" method="POST" action="{{ route('settings.sidebarManager.groups.assign') }}">
    @csrf
    <input type="hidden" id="sb-assign-item-key" name="item_key" value="">
    <input type="hidden" id="sb-assign-section-key" name="section_key" value="">
</form>

<style>
    /* Colors use the admin layout's theme variables so this follows light/dark mode. */
    .sb-card{margin-bottom:1rem}
    .sb-card-head{display:flex;align-items:center;justify-content:space-between;gap:.6rem;margin-bottom:.9rem}
    .sb-card-head h2{font-size:1rem;margin:0;display:flex;align-items:center;gap:.4rem}
    .inline{display:inline}
    .sb-hint{color:var(--muted);font-size:.85rem;margin:-.4rem 0 1rem}

    .sb-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:.5rem}
    .sb-group-list{min-height:2.75rem}
    .sb-item{display:flex;align-items:center;gap:.6rem;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:.55rem .8rem;color:var(--text)}
    .sb-item.dragging{opacity:.5}
    .sb-drag{cursor:grab;color:var(--muted);font-size:1rem;user-select:none}
    .sb-ico{font-size:1.05rem}
    .sb-label{font-size:.92rem;flex:1}
    .sb-actions{margin-top:.9rem}
    .sb-delete-btn{margin-left:.4rem;padding:.25rem .5rem}
    .sb-delete-btn:hover{color:#fff;background:var(--danger);border-color:var(--danger)}
    .sb-hidden-form{display:none}

    .sb-hide-btn{margin-left:auto;padding:.25rem .5rem;font-size:.8rem;white-space:nowrap}
    .sb-hide-btn.is-hidden{color:#fff;background:var(--danger);border-color:var(--danger)}

    .sb-item-sm{padding:.4rem .7rem}
    .sb-link-list{gap:.35rem}
    .sb-links-wrap{margin-top:1rem;padding-top:.8rem;border-top:1px dashed var(--border);display:flex;flex-direction:column;gap:.9rem}
    .sb-links-block{padding-left:.4rem}
    .sb-links-head{display:flex;align-items:baseline;gap:.4rem;font-size:.82rem;color:var(--muted);margin-bottom:.4rem}
    .sb-links-sub{font-size:.7rem;text-transform:uppercase;letter-spacing:.03em;opacity:.75}
    .sb-links-empty{color:var(--muted);font-size:.85rem;margin:.2rem 0 0}
</style>

<script>
(function () {
    var dragging = null;

    function itemsOf(list) {
        return Array.prototype.slice.call(list.querySelectorAll(':scope > li'));
    }

    function reposition(list, e) {
        var itemsHere = itemsOf(list);
        var after = null;
        itemsHere.forEach(function (x) {
            if (x === dragging) return;
            var box = x.getBoundingClientRect();
            if (e.clientY > box.top + box.height / 2) after = x;
        });

        if (after == null) {
            var first = itemsHere.filter(function (x) { return x !== dragging; })[0];
            if (first) {
                list.insertBefore(dragging, first);
            } else {
                list.appendChild(dragging);
            }
        } else {
            after.parentNode.insertBefore(dragging, after.nextSibling);
        }
    }

    // Link lists are per sub-group (every group's <ul> shares data-kind="link"), so a link
    // must only be allowed to reposition within the SAME group's own list — otherwise the
    // generic data-kind matching below would let a link be dragged into a different group's list.
    function sameGroupScope(list) {
        if (list.dataset.kind !== 'link') return true;
        return !!dragging && dragging.getAttribute('data-group-key') === list.getAttribute('data-group-key');
    }

    function initDragList(list) {
        var crossSection = list.hasAttribute('data-sb-group-list');

        itemsOf(list).forEach(function (item) {
            item.addEventListener('dragstart', function () {
                dragging = item;
                item.classList.add('dragging');
            });
            item.addEventListener('dragend', function () {
                item.classList.remove('dragging');
                dragging = null;
            });
        });

        list.addEventListener('dragover', function (e) {
            if (!dragging || dragging.dataset.kind !== list.dataset.kind) return;
            if (!sameGroupScope(list)) return;
            e.preventDefault();
            reposition(list, e);
        });

        list.addEventListener('drop', function (e) {
            if (!dragging || dragging.dataset.kind !== list.dataset.kind) return;
            if (!sameGroupScope(list)) return;
            e.preventDefault();
            if (!crossSection) return;

            var originSectionKey = dragging.getAttribute('data-section-key');
            var targetSectionKey = list.getAttribute('data-section-key');
            if (originSectionKey && targetSectionKey && originSectionKey !== targetSectionKey) {
                document.getElementById('sb-assign-item-key').value = dragging.getAttribute('data-item-key');
                document.getElementById('sb-assign-section-key').value = targetSectionKey;
                document.getElementById('sb-assign-form').submit();
            }
        });
    }

    document.querySelectorAll('[data-sb-list]').forEach(initDragList);
})();
</script>
@endsection
