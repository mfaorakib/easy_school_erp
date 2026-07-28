@extends('layouts.admin')
@section('title', 'Complaints')

@section('content')
<div class="page-head"><h1>{{ $complaint->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.complaints') }}</h1>
    <a href="{{ route('frontoffice.complaints.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:820px">
    <form method="POST" enctype="multipart/form-data" action="{{ $complaint->exists ? route('frontoffice.complaints.update', $complaint) : route('frontoffice.complaints.store') }}">
        @csrf @if($complaint->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>Complainant Name *</label>
                <input name="complainant_name" type="text" maxlength="255" value="{{ old('complainant_name', $complaint->complainant_name) }}" required>
                @error('complainant_name')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div><label>{{ __('ui.mobile') }}</label>
                <input name="phone" type="text" maxlength="40" value="{{ old('phone', $complaint->phone) }}">
                @error('phone')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div><label>Type</label>
                <select name="complaint_type_id">
                    <option value="">—</option>
                    @foreach($types as $t)<option value="{{ $t->id }}" {{ old('complaint_type_id', $complaint->complaint_type_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach
                </select>
                @error('complaint_type_id')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div><label>{{ __('ui.source') }}</label>
                <input name="source" type="text" maxlength="255" value="{{ old('source', $complaint->source) }}">
                @error('source')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div><label>Date *</label>
                <input name="complaint_date" type="date" value="{{ old('complaint_date', optional($complaint->complaint_date)->format('Y-m-d') ?: now()->format('Y-m-d')) }}" required>
                @error('complaint_date')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div><label>{{ __('ui.assigned_to') }}</label>
                <select name="assigned_to">
                    <option value="">—</option>
                    @foreach($staff as $s)<option value="{{ $s->id }}" {{ old('assigned_to', $complaint->assigned_to) == $s->id ? 'selected' : '' }}>{{ $s->displayName() }}</option>@endforeach
                </select>
                @error('assigned_to')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div><label>{{ __('ui.status') }}</label>
                <select name="status">
                    <option value="open" {{ old('status', $complaint->status) === 'open' ? 'selected' : '' }}>{{ __('ui.open') }}</option>
                    <option value="in_progress" {{ old('status', $complaint->status) === 'in_progress' ? 'selected' : '' }}>{{ __('ui.in_progress') }}</option>
                    <option value="resolved" {{ old('status', $complaint->status) === 'resolved' ? 'selected' : '' }}>{{ __('ui.resolved') }}</option>
                </select>
                @error('status')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div style="grid-column:1/-1"><label>Description</label>
                <textarea name="description" rows="3">{{ old('description', $complaint->description) }}</textarea>
                @error('description')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div style="grid-column:1/-1"><label>{{ __('ui.action_taken') }}</label>
                <textarea name="action_taken" rows="3">{{ old('action_taken', $complaint->action_taken) }}</textarea>
                @error('action_taken')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div style="grid-column:1/-1"><label>Attachment</label>
                <input name="attachment" type="file">
                @error('attachment')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $complaint->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
