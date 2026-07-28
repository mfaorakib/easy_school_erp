@extends('layouts.admin')
@section('title', __('ui.menus'))

@section('content')
<div class="page-head">
    <h1>{{ __('ui.menus') }}</h1>
    <div class="actions">
        <a class="btn btn-sm btn-ghost" href="{{ url('/') }}" target="_blank" rel="noopener">{{ __('ui.view_site') }}</a>
    </div>
</div>

@foreach([$header, $footer] as $menu)
<div class="card">
    <div class="page-head">
        <h1>
            {{ $menu->title }}
            <span class="badge">{{ $menu->location === 'header' ? __('ui.header') : __('ui.footer') }}</span>
        </h1>
    </div>

    @if($menu->items->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <table>
        <thead>
            <tr>
                <th>{{ __('ui.label') }}</th>
                <th>{{ __('ui.link') }}</th>
                <th>New tab</th>
                <th>{{ __('ui.actions') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($menu->items as $item)
            <tr>
                <td>{{ $item->label }}</td>
                <td>{{ optional($item->page)->title ? '↗ '.$item->page->title : ($item->url ?: '—') }}</td>
                <td>{{ $item->open_new_tab ? 'Yes' : '—' }}</td>
                <td class="actions">
                    <form method="POST" action="{{ route('builder.menus.items.move', $item) }}" style="display:inline">
                        @csrf
                        <input type="hidden" name="direction" value="up">
                        <button class="btn btn-sm btn-ghost" type="submit" title="{{ __('ui.move_up') }}">{{ __('ui.move_up') }}</button>
                    </form>
                    <form method="POST" action="{{ route('builder.menus.items.move', $item) }}" style="display:inline">
                        @csrf
                        <input type="hidden" name="direction" value="down">
                        <button class="btn btn-sm btn-ghost" type="submit" title="{{ __('ui.move_down') }}">{{ __('ui.move_down') }}</button>
                    </form>
                    <form method="POST" action="{{ route('builder.menus.items.remove', $item) }}" style="display:inline"
                          onsubmit="return confirm('{{ __('ui.delete') }}?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" type="submit">{{ __('ui.delete') }}</button>
                    </form>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <form method="POST" action="{{ route('builder.menus.items.update', $item) }}">
                        @csrf
                        @method('PUT')
                        <div class="grid">
                            <div>
                                <label>{{ __('ui.label') }}</label>
                                <input type="text" name="label" maxlength="120" value="{{ $item->label }}" required>
                            </div>
                            <div>
                                <label>{{ __('ui.pages') }}</label>
                                <select name="page_id">
                                    <option value="">— {{ __('ui.link') }} URL —</option>
                                    @foreach($pages as $p)
                                        <option value="{{ $p->id }}" {{ (int) $item->page_id === (int) $p->id ? 'selected' : '' }}>{{ $p->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label>{{ __('ui.link') }}</label>
                                <input type="text" name="url" maxlength="255" value="{{ $item->url }}" placeholder="/p/slug or https://…">
                            </div>
                            <div>
                                <label>New tab</label>
                                <input type="checkbox" name="open_new_tab" value="1" {{ $item->open_new_tab ? 'checked' : '' }}>
                            </div>
                            <div style="align-self:end">
                                <button class="btn btn-sm" type="submit">{{ __('ui.save') }}</button>
                            </div>
                        </div>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif

    <form method="POST" action="{{ route('builder.menus.items.add', $menu) }}">
        @csrf
        <div class="grid">
            <div>
                <label>{{ __('ui.label') }} *</label>
                <input type="text" name="label" maxlength="120" value="{{ old('label') }}" required>
                @error('label')<span class="badge">{{ $message }}</span>@enderror
            </div>
            <div>
                <label>{{ __('ui.pages') }}</label>
                <select name="page_id">
                    <option value="">— {{ __('ui.link') }} URL —</option>
                    @foreach($pages as $p)
                        <option value="{{ $p->id }}">{{ $p->title }}</option>
                    @endforeach
                </select>
                @error('page_id')<span class="badge">{{ $message }}</span>@enderror
            </div>
            <div>
                <label>{{ __('ui.link') }}</label>
                <input type="text" name="url" maxlength="255" value="{{ old('url') }}" placeholder="/p/slug or https://…">
                @error('url')<span class="badge">{{ $message }}</span>@enderror
            </div>
            <div>
                <label>New tab</label>
                <input type="checkbox" name="open_new_tab" value="1">
            </div>
            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">{{ __('ui.add_item') }}</button>
            </div>
        </div>
        <p class="empty">Pick a page OR enter a URL.</p>
    </form>
</div>
@endforeach
@endsection
