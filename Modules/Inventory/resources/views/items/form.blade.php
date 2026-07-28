@extends('layouts.admin')
@section('title', __('ui.items'))

@section('content')
<div class="page-head"><h1>{{ $item->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.items') }}</h1>
    <a href="{{ route('inventory.items.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $item->exists ? route('inventory.items.update', $item) : route('inventory.items.store') }}">
        @csrf @if($item->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>Name *</label><input name="name" value="{{ old('name', $item->name) }}" required></div>
            <div><label>Category</label>
                <select name="item_category_id"><option value="">—</option>
                    @foreach($categories as $c)<option value="{{ $c->id }}" {{ old('item_category_id', $item->item_category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                </select></div>
            <div><label>Unit</label><input name="unit" value="{{ old('unit', $item->unit) }}"></div>
            <div><label>Description</label><input name="description" value="{{ old('description', $item->description) }}"></div>
        </div>
        @if($item->exists)<p class="empty" style="text-align:left;color:var(--muted);padding:.5rem 0 0">Current stock: {{ $item->stock }}</p>@endif
        <div style="margin-top:1.25rem"><button class="btn">{{ $item->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
