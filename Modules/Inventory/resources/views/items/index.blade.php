@extends('layouts.admin')
@section('title', __('ui.items'))

@section('content')
<div class="page-head"><h1>{{ __('ui.items') }}</h1>
    <a href="{{ route('inventory.items.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($items->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Name</th><th>Category</th><th>Unit</th><th>Stock</th><th></th></tr></thead>
        <tbody>
        @foreach($items as $item)
            <tr>
                <td><strong>{{ $item->name }}</strong></td>
                <td>{{ optional($item->category)->name ?? '—' }}</td>
                <td>{{ $item->unit ?? '—' }}</td>
                <td><span class="badge">{{ $item->stock }}</span></td>
                <td class="actions">
                    <a href="{{ route('inventory.items.edit', $item) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('inventory.items.destroy', $item) }}" onsubmit="return confirm('Delete?')">
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
