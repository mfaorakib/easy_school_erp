@extends('layouts.admin')
@section('title', 'Download Content')

@section('content')
<div class="page-head"><h1>Download Content</h1>
    <a href="{{ route('downloadcenter.contents.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($contents->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Title</th><th>Type</th><th>Class / Section</th><th>Audience</th><th>Link</th><th></th></tr></thead>
        <tbody>
        @foreach($contents as $content)
            <tr>
                <td><strong>{{ $content->title }}</strong></td>
                <td>{{ optional($content->type)->name ?: '—' }}</td>
                <td>
                    @if($content->class_id || $content->section_id)
                        {{ optional($content->schoolClass)->name ?: 'Any' }} / {{ optional($content->section)->name ?: 'Any' }}
                    @else
                        All
                    @endif
                </td>
                <td>
                    @forelse($content->audiences ?? [] as $audience)
                        <span class="badge">{{ $audience }}</span>
                    @empty
                        —
                    @endforelse
                </td>
                <td>
                    @if($content->link())
                        <a href="{{ $content->link() }}" target="_blank">Open</a>
                    @else
                        —
                    @endif
                </td>
                <td class="actions">
                    <a href="{{ route('downloadcenter.contents.edit', $content) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('downloadcenter.contents.destroy', $content) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')<button class="btn btn-sm btn-danger">{{ __('ui.delete') }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
