@extends('layouts.admin')
@section('title', 'Dormitories')

@section('content')
<div class="page-head"><h1>{{ $dormitory->exists ? __('ui.edit') : __('ui.add') }} — Dormitories</h1>
    <a href="{{ route('dormitory.dormitories.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $dormitory->exists ? route('dormitory.dormitories.update', $dormitory) : route('dormitory.dormitories.store') }}">
        @csrf @if($dormitory->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.name') }} *</label><input name="name" value="{{ old('name', $dormitory->name) }}" required></div>
            <div><label>Type *</label>
                <select name="type" required>
                    @foreach(['boys', 'girls', 'mixed'] as $type)
                        <option value="{{ $type }}" @selected(old('type', $dormitory->type) === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div style="grid-column:1/-1"><label>Address</label><input name="address" value="{{ old('address', $dormitory->address) }}"></div>
            <div style="grid-column:1/-1"><label>Note</label><textarea name="note" rows="3">{{ old('note', $dormitory->note) }}</textarea></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $dormitory->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
