@extends('layouts.admin')
@section('title', 'Enquiries')

@section('content')
<div class="page-head">
    <h1>{{ $enquiry->name }}
        @if($enquiry->status === 'won')
            <span class="badge">{{ __('ui.won') }}</span>
        @elseif($enquiry->status === 'lost')
            <span class="badge">{{ __('ui.lost') }}</span>
        @else
            <span class="badge">{{ __('ui.active_lead') }}</span>
        @endif
    </h1>
    <div class="actions">
        <a href="{{ route('frontoffice.enquiries.edit', $enquiry) }}" class="btn btn-sm">{{ __('ui.edit') }}</a>
        <a href="{{ route('frontoffice.enquiries.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a>
    </div>
</div>

<div class="card">
    <table>
        <tbody>
            <tr><th>{{ __('ui.mobile') }}</th><td>{{ $enquiry->phone ?: '—' }}</td></tr>
            <tr><th>Email</th><td>{{ $enquiry->email ?: '—' }}</td></tr>
            <tr><th>{{ __('ui.classes') }}</th><td>{{ optional($enquiry->schoolClass)->name ?: '—' }}</td></tr>
            <tr><th>{{ __('ui.source') }}</th><td>{{ $enquiry->source ?: '—' }}</td></tr>
            <tr><th>{{ __('ui.assigned_to') }}</th><td>{{ $enquiry->assignee ? $enquiry->assignee->displayName() : '—' }}</td></tr>
            <tr><th>Enquiry Date</th><td>{{ $enquiry->enquiry_date?->format('d M Y') ?: '—' }}</td></tr>
            <tr><th>{{ __('ui.next_follow_up') }}</th><td>{{ $enquiry->next_follow_up_date?->format('d M Y') ?: '—' }}</td></tr>
            <tr><th>Description</th><td>{{ $enquiry->description ?: '—' }}</td></tr>
        </tbody>
    </table>
</div>

<div class="card">
    <div class="page-head"><h1>{{ __('ui.followups') }}</h1></div>

    <form method="POST" action="{{ route('frontoffice.enquiries.followups.add', $enquiry) }}">
        @csrf
        <div class="grid">
            <div><label>{{ __('ui.follow_up') }} Date</label><input name="follow_up_date" type="date" value="{{ old('follow_up_date', now()->format('Y-m-d')) }}" required></div>
            <div><label>{{ __('ui.next_follow_up') }}</label><input name="next_follow_up_date" type="date" value="{{ old('next_follow_up_date') }}"></div>
            <div><label>Response</label><input name="response" type="text" value="{{ old('response') }}"></div>
            <div><label>Note</label><input name="note" type="text" maxlength="255" value="{{ old('note') }}"></div>
            <div style="align-self:end"><button class="btn btn-sm" type="submit">{{ __('ui.add_followup') }}</button></div>
        </div>
    </form>

    @if($enquiry->followups->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>Date</th>
            <th>Response</th>
            <th>Next</th>
            <th>By</th>
        </tr></thead>
        <tbody>
        @foreach($enquiry->followups as $f)
            <tr>
                <td>{{ $f->follow_up_date?->format('d M Y') }}</td>
                <td>{{ $f->response ?: '—' }}</td>
                <td>{{ $f->next_follow_up_date?->format('d M Y') ?: '—' }}</td>
                <td>{{ optional($f->creator)->name ?: '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
