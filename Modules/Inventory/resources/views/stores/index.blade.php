@extends('layouts.admin')
@section('title', 'Stores')

@section('content')
<div class="page-head"><h1>Stores</h1>
    <a href="{{ route('inventory.stores.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($stores->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>Code</th><th></th></tr></thead>
        <tbody>
        @foreach($stores as $store)
            <tr>
                <td><strong>{{ $store->name }}</strong></td>
                <td>{{ $store->code }}</td>
                <td class="actions">
                    <a href="{{ route('inventory.stores.edit', $store) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('inventory.stores.destroy', $store) }}" onsubmit="return confirm('Delete?')">
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
