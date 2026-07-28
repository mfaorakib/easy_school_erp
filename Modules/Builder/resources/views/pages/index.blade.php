@extends('layouts.admin')
@section('title', 'Pages')

@section('content')
<div class="page-head"><h1>{{ __('ui.pages') }}</h1>
    <div class="actions">
        <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-ghost">{{ __('ui.view_site') }}</a>
        <a href="{{ route('builder.pages.create') }}" class="btn">+ {{ __('ui.add') }}</a>
    </div>
</div>

<div class="card">
    @if($pages->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <table>
        <thead><tr>
            <th>{{ __('ui.page_title') }}</th>
            <th>{{ __('ui.slug') }}</th>
            <th>{{ __('ui.status') }}</th>
            <th>{{ __('ui.blocks') }}</th>
            <th>{{ __('ui.actions') }}</th>
        </tr></thead>
        <tbody>
        @foreach($pages as $page)
            <tr>
                <td><strong>{{ $page->title }}</strong></td>
                <td><code>{{ $page->is_home ? '/' : '/p/' . $page->slug }}</code></td>
                <td>
                    @if($page->is_home)<span class="badge">{{ __('ui.home_page') }}</span>@endif
                    @if($page->is_published)<span class="badge">{{ __('ui.published') }}</span>@else<span class="badge">Draft</span>@endif
                </td>
                <td><span class="badge">{{ $page->blocks()->count() }}</span></td>
                <td class="actions">
                    <a href="{{ route('builder.blocks.index', $page) }}" class="btn btn-sm btn-ghost">{{ __('ui.blocks') }}</a>
                    <a href="{{ route('builder.pages.edit', $page) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    @unless($page->is_home)
                        <form method="POST" action="{{ route('builder.pages.home', $page) }}" style="display:inline">
                            @csrf<button class="btn btn-sm">{{ __('ui.set_home') }}</button>
                        </form>
                    @endunless
                    <form method="POST" action="{{ route('builder.pages.destroy', $page) }}" style="display:inline" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')<button class="btn btn-sm btn-danger">{{ __('ui.delete') }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
