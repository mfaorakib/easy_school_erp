@extends('layouts.admin')
@section('title', 'Holidays')

@section('content')
<div class="page-head"><h1>{{ $holiday->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.holidays') }}</h1>
    <a href="{{ route('settings.holidays.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $holiday->exists ? route('settings.holidays.update', $holiday) : route('settings.holidays.store') }}">
        @csrf @if($holiday->exists) @method('PUT') @endif
        <div class="grid">
            <div style="grid-column:1/-1"><label>{{ __('ui.name') }} *</label>
                <input name="title" type="text" maxlength="255" value="{{ old('title', $holiday->title) }}" required>
                @error('title')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>
            <div><label>From *</label>
                <input name="from_date" type="date" value="{{ old('from_date', optional($holiday->from_date)->format('Y-m-d')) }}" required>
                @error('from_date')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>
            <div><label>To *</label>
                <input name="to_date" type="date" value="{{ old('to_date', optional($holiday->to_date)->format('Y-m-d')) }}" required>
                @error('to_date')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>
            <div style="grid-column:1/-1"><label>Description</label>
                <input name="description" type="text" maxlength="255" value="{{ old('description', $holiday->description) }}">
                @error('description')<small style="color:#dc2626">{{ $message }}</small>@enderror
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $holiday->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
