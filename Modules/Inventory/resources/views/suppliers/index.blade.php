@extends('layouts.admin')
@section('title', 'Suppliers')

@section('content')
<div class="page-head"><h1>Suppliers</h1>
    <a href="{{ route('inventory.suppliers.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($suppliers->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>Phone</th><th>Email</th><th>Contact Person</th><th></th></tr></thead>
        <tbody>
        @foreach($suppliers as $supplier)
            <tr>
                <td><strong>{{ $supplier->name }}</strong></td>
                <td>{{ $supplier->phone }}</td>
                <td>{{ $supplier->email }}</td>
                <td>{{ $supplier->contact_person }}</td>
                <td class="actions">
                    <a href="{{ route('inventory.suppliers.edit', $supplier) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('inventory.suppliers.destroy', $supplier) }}" onsubmit="return confirm('Delete?')">
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
