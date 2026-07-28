@extends('layouts.admin')
@section('title', 'Behaviour Types')

@section('content')
<div class="page-head"><h1>Behaviour Types</h1>
    <a href="{{ route('behaviour.types.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($types->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Title</th><th>Point</th><th></th></tr></thead>
        <tbody>
        @foreach($types as $type)
            <tr>
                <td><strong>{{ $type->title }}</strong></td>
                <td>
                    @if($type->point >= 0)
                        <span class="badge" style="color:var(--success)">+{{ $type->point }}</span>
                    @else
                        <span class="badge" style="color:var(--danger)">{{ $type->point }}</span>
                    @endif
                </td>
                <td class="actions">
                    <a href="{{ route('behaviour.types.edit', $type) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('behaviour.types.destroy', $type) }}" onsubmit="return confirm('Delete?')">
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
