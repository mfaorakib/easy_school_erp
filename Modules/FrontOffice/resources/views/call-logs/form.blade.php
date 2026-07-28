@extends('layouts.admin')
@section('title', 'Call Log')

@section('content')
<div class="page-head"><h1>{{ $log->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.call_log') }}</h1>
    <a href="{{ route('frontoffice.callLogs.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $log->exists ? route('frontoffice.callLogs.update', $log) : route('frontoffice.callLogs.store') }}">
        @csrf @if($log->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.name') }} *</label><input name="name" type="text" maxlength="255" value="{{ old('name', $log->name) }}" required></div>
            <div><label>{{ __('ui.mobile') }} *</label><input name="phone" type="text" maxlength="40" value="{{ old('phone', $log->phone) }}" required></div>
            <div><label>{{ __('ui.call_type') }}</label>
                <select name="call_type">
                    <option value="incoming" {{ old('call_type', $log->call_type) === 'incoming' ? 'selected' : '' }}>{{ __('ui.incoming') }}</option>
                    <option value="outgoing" {{ old('call_type', $log->call_type) === 'outgoing' ? 'selected' : '' }}>{{ __('ui.outgoing') }}</option>
                </select>
            </div>
            <div><label>Date *</label><input name="call_date" type="date" value="{{ old('call_date', optional($log->call_date)->format('Y-m-d') ?: ($log->exists ? '' : now()->format('Y-m-d'))) }}" required></div>
            <div><label>Duration</label><input name="call_duration" type="text" maxlength="40" placeholder="e.g. 5 min" value="{{ old('call_duration', $log->call_duration) }}"></div>
            <div><label>{{ __('ui.next_follow_up') }}</label><input name="next_follow_up_date" type="date" value="{{ old('next_follow_up_date', optional($log->next_follow_up_date)->format('Y-m-d')) }}"></div>
            <div style="grid-column:1/-1"><label>Description</label><textarea name="description" rows="4">{{ old('description', $log->description) }}</textarea></div>
            <div style="grid-column:1/-1"><label>Note</label><input name="note" type="text" maxlength="255" value="{{ old('note', $log->note) }}"></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $log->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
