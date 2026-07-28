@extends('layouts.admin')
@section('title', 'Testimonials')

@section('content')
<div class="page-head">
    <h1>{{ __('ui.testimonials') }}</h1>
    <div style="display:flex;gap:.5rem">
        <a href="{{ url('/') }}" target="_blank" class="btn btn-ghost">{{ __('ui.view_site') }} ↗</a>
        <a href="{{ route('builder.testimonials.create') }}" class="btn">+ {{ __('ui.add') }}</a>
    </div>
</div>

<div class="card">
    @if($testimonials->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <table>
        <thead><tr><th>Photo</th><th>{{ __('ui.name') }}</th><th>Designation</th><th>{{ __('ui.rating') }}</th><th>Quote</th><th>{{ __('ui.status') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($testimonials as $t)
            <tr>
                <td>@if($t->photo_path)<img src="{{ \Modules\Builder\Services\BuilderService::media($t->photo_path) }}" style="width:40px;height:40px;object-fit:cover;border-radius:50%">@else — @endif</td>
                <td><strong>{{ $t->name }}</strong></td>
                <td>{{ $t->designation }}@if($t->organization), {{ $t->organization }}@endif</td>
                <td><span class="badge">{{ str_repeat('★', (int) $t->rating) }}</span></td>
                <td>{{ \Illuminate\Support\Str::limit($t->quote, 50) }}</td>
                <td><span class="badge">{{ $t->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="actions">
                    <a href="{{ route('builder.testimonials.edit', $t) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('builder.testimonials.destroy', $t) }}" style="display:inline" onsubmit="return confirm('{{ __('ui.delete') }}?')">
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
