@extends('layouts.admin')
@section('title', 'Item Categories')

@section('content')
<div class="page-head"><h1>Item Categories</h1>
    <a href="{{ route('inventory.categories.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($categories->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($categories as $category)
            <tr>
                <td><strong>{{ $category->name }}</strong></td>
                <td class="actions">
                    <a href="{{ route('inventory.categories.edit', $category) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('inventory.categories.destroy', $category) }}" onsubmit="return confirm('Delete?')">
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
