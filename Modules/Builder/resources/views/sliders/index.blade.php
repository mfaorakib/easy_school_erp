@extends('layouts.admin')
@section('title', 'Sliders')

@section('content')
<div class="page-head">
    <h1>{{ __('ui.sliders') }}</h1>
    <div style="display:flex;gap:.5rem">
        <a href="{{ url('/') }}" target="_blank" class="btn btn-ghost">{{ __('ui.view_site') }} ↗</a>
        <a href="{{ route('builder.sliders.create') }}" class="btn">+ {{ __('ui.add') }}</a>
    </div>
</div>

<div class="card">
    @if($sliders->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <table>
        <thead><tr><th>{{ __('ui.image') }}</th><th>Title</th><th>Subtitle</th><th>Link</th><th>Position</th><th>{{ __('ui.status') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($sliders as $slider)
            <tr>
                <td>@if($slider->image_path)<img src="{{ \Modules\Builder\Services\BuilderService::media($slider->image_path) }}" style="width:64px;height:40px;object-fit:cover;border-radius:6px">@else — @endif</td>
                <td><strong>{{ $slider->title ?: '—' }}</strong></td>
                <td>{{ $slider->subtitle ?: '—' }}</td>
                <td>{{ $slider->link_url ?: '—' }}</td>
                <td>{{ $slider->position }}</td>
                <td><span class="badge">{{ $slider->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="actions">
                    <a href="{{ route('builder.sliders.edit', $slider) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('builder.sliders.destroy', $slider) }}" style="display:inline" onsubmit="return confirm('{{ __('ui.delete') }}?')">
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
