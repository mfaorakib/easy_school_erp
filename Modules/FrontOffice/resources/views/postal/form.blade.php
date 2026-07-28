@extends('layouts.admin')
@section('title', 'Postal')

@section('content')
<div class="page-head">
    <h1>{{ $record->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.postal') }}</h1>
    <a href="{{ route('frontoffice.postal.index', ['type' => $record->type ?: 'dispatch']) }}" class="btn btn-ghost">{{ __('ui.back') }}</a>
</div>

<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $record->exists ? route('frontoffice.postal.update', $record) : route('frontoffice.postal.store') }}" enctype="multipart/form-data">
        @csrf @if($record->exists) @method('PUT') @endif
        <div class="grid">
            <div>
                <label>Type *</label>
                <select name="type" required>
                    <option value="dispatch" {{ old('type', $record->type) === 'dispatch' ? 'selected' : '' }}>{{ __('ui.dispatch') }}</option>
                    <option value="receive" {{ old('type', $record->type) === 'receive' ? 'selected' : '' }}>{{ __('ui.receive') }}</option>
                </select>
                @error('type')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>Title *</label>
                <input name="title" type="text" maxlength="255" value="{{ old('title', $record->title) }}" required>
                @error('title')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>To / From</label>
                <input name="party" type="text" maxlength="255" value="{{ old('party', $record->party) }}">
                @error('party')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>Reference No</label>
                <input name="reference_no" type="text" maxlength="255" value="{{ old('reference_no', $record->reference_no) }}">
                @error('reference_no')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>Address</label>
                <input name="address" type="text" maxlength="255" value="{{ old('address', $record->address) }}">
                @error('address')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>Date *</label>
                <input name="postal_date" type="date" value="{{ old('postal_date', optional($record->postal_date)->format('Y-m-d') ?: now()->format('Y-m-d')) }}" required>
                @error('postal_date')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div style="grid-column:1/-1">
                <label>Note</label>
                <textarea name="note" rows="3">{{ old('note', $record->note) }}</textarea>
                @error('note')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div style="grid-column:1/-1">
                <label>Attachment</label>
                <input type="file" name="attachment">
                @if($record->exists && $record->attachment_path)
                    <div style="margin-top:.35rem"><a href="{{ asset('storage/'.$record->attachment_path) }}">file</a></div>
                @endif
                @error('attachment')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $record->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
