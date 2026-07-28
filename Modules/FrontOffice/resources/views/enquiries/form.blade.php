@extends('layouts.admin')
@section('title', 'Enquiries')

@section('content')
<div class="page-head"><h1>{{ $enquiry->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.enquiries') }}</h1>
    <a href="{{ route('frontoffice.enquiries.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $enquiry->exists ? route('frontoffice.enquiries.update', $enquiry) : route('frontoffice.enquiries.store') }}">
        @csrf @if($enquiry->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.name') }} *</label><input name="name" type="text" maxlength="255" value="{{ old('name', $enquiry->name) }}" required></div>
            <div><label>{{ __('ui.mobile') }}</label><input name="phone" type="text" maxlength="40" value="{{ old('phone', $enquiry->phone) }}"></div>
            <div><label>Email</label><input name="email" type="email" value="{{ old('email', $enquiry->email) }}"></div>
            <div><label>Address</label><input name="address" type="text" maxlength="255" value="{{ old('address', $enquiry->address) }}"></div>
            <div><label>{{ __('ui.classes') }}</label>
                <select name="class_id">
                    <option value="">—</option>
                    @foreach($classes as $c)<option value="{{ $c->id }}" {{ old('class_id', $enquiry->class_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div><label>{{ __('ui.source') }}</label><input name="source" type="text" maxlength="255" value="{{ old('source', $enquiry->source) }}"></div>
            <div><label>No. of Children</label><input name="no_of_child" type="number" min="1" value="{{ old('no_of_child', $enquiry->no_of_child ?? 1) }}"></div>
            <div><label>Enquiry Date *</label><input name="enquiry_date" type="date" value="{{ old('enquiry_date', optional($enquiry->enquiry_date)->format('Y-m-d') ?: ($enquiry->exists ? '' : now()->format('Y-m-d'))) }}" required></div>
            <div><label>{{ __('ui.next_follow_up') }}</label><input name="next_follow_up_date" type="date" value="{{ old('next_follow_up_date', optional($enquiry->next_follow_up_date)->format('Y-m-d')) }}"></div>
            <div><label>{{ __('ui.assigned_to') }}</label>
                <select name="assigned_to">
                    <option value="">—</option>
                    @foreach($staff as $s)<option value="{{ $s->id }}" {{ old('assigned_to', $enquiry->assigned_to) == $s->id ? 'selected' : '' }}>{{ $s->displayName() }}</option>@endforeach
                </select>
            </div>
            <div><label>{{ __('ui.status') }}</label>
                <select name="status" required>
                    <option value="active" {{ old('status', $enquiry->status) == 'active' ? 'selected' : '' }}>{{ __('ui.active_lead') }}</option>
                    <option value="won" {{ old('status', $enquiry->status) == 'won' ? 'selected' : '' }}>{{ __('ui.won') }}</option>
                    <option value="lost" {{ old('status', $enquiry->status) == 'lost' ? 'selected' : '' }}>{{ __('ui.lost') }}</option>
                </select>
            </div>
            <div style="grid-column:1/-1"><label>Description</label><textarea name="description" rows="4">{{ old('description', $enquiry->description) }}</textarea></div>
            <div style="grid-column:1/-1"><label>Note</label><input name="note" type="text" maxlength="255" value="{{ old('note', $enquiry->note) }}"></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $enquiry->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
