@extends('layouts.admin')
@section('title', 'Account Heads')

@section('content')
<div class="page-head"><h1>{{ $head->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.account_heads') }}</h1>
    <a href="{{ route('accounting.heads.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $head->exists ? route('accounting.heads.update', $head) : route('accounting.heads.store') }}">
        @csrf @if($head->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.name') }} *</label><input name="name" type="text" maxlength="200" value="{{ old('name', $head->name) }}" required></div>
            <div><label>Type</label>
                <select name="type">
                    <option value="income" {{ old('type', $head->type) === 'income' ? 'selected' : '' }}>{{ __('ui.income') }}</option>
                    <option value="expense" {{ old('type', $head->type) === 'expense' ? 'selected' : '' }}>{{ __('ui.expense') }}</option>
                </select>
            </div>
            <div style="grid-column:1/-1"><label>Description</label><textarea name="description" rows="4">{{ old('description', $head->description) }}</textarea></div>
            <div><label>{{ __('ui.status') }}</label>
                <label style="font-weight:normal"><input name="is_active" type="checkbox" value="1" {{ old('is_active', $head->exists ? $head->is_active : true) ? 'checked' : '' }}> Active</label>
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $head->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
