@extends('layouts.admin')
@section('title', 'Content Types')

@section('content')
<div class="page-head"><h1>Content Types</h1>
    <a href="{{ route('downloadcenter.types.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($types->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($types as $type)
            <tr>
                <td><strong>{{ $type->name }}</strong></td>
                <td class="actions">
                    <a href="{{ route('downloadcenter.types.edit', $type) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('downloadcenter.types.destroy', $type) }}" onsubmit="return confirm('Delete?')">
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
