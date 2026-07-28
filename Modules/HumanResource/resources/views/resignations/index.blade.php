@extends('layouts.admin')
@section('title', __('ui.resignations'))

@section('content')
<div class="page-head"><h1>{{ __('ui.resignations') }}</h1></div>

<div class="card">
    <h3 style="margin-top:0">{{ __('ui.approvals') }}</h3>
    @if($pending->isEmpty())
        <div class="empty">{{ __('ui.no_resignations') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>{{ __('ui.staff') }}</th>
                <th>{{ __('ui.intended_last_day') }}</th>
                <th>{{ __('ui.reason') }}</th>
                <th>{{ __('ui.actions') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($pending as $a)
            <tr>
                <td><strong>{{ optional($a->staff)->displayName() }}</strong></td>
                <td>{{ $a->intended_last_day?->format('d M Y') }}</td>
                <td>{{ \Illuminate\Support\Str::limit($a->reason, 60) }}</td>
                <td class="actions">
                    <form method="POST" action="{{ route('hr.resignations.review', $a) }}" style="display:inline">
                        @csrf
                        <input type="hidden" name="status" value="approved">
                        <button class="btn btn-sm" type="submit" onclick="return confirm('Approve this resignation? The staff member will be marked inactive with this leaving date.')">{{ __('ui.approve') }}</button>
                    </form>
                    <form method="POST" action="{{ route('hr.resignations.review', $a) }}" style="display:inline">
                        @csrf
                        <input type="hidden" name="status" value="rejected">
                        <input type="text" name="review_note" maxlength="255" placeholder="note">
                        <button class="btn btn-sm btn-danger" type="submit">{{ __('ui.reject') }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>

<div class="card">
    <h3 style="margin-top:0">{{ __('ui.history') }}</h3>
    @if($history->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.staff') }}</th><th>{{ __('ui.intended_last_day') }}</th><th>{{ __('ui.status') }}</th><th>{{ __('ui.reviewed_by') }}</th></tr></thead>
        <tbody>
        @foreach($history as $a)
            <tr>
                <td>{{ optional($a->staff)->displayName() }}</td>
                <td>{{ $a->intended_last_day?->format('d M Y') }}</td>
                <td><span class="badge">{{ ucfirst($a->status) }}</span></td>
                <td>{{ optional($a->reviewer)->name }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
