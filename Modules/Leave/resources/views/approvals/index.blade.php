@extends('layouts.admin')
@section('title', __('ui.approvals'))

@section('content')
<div class="page-head"><h1>{{ __('ui.approvals') }}</h1></div>

<div class="card">
    @if($pending->isEmpty())
        <div class="empty">No pending applications.</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>Staff</th>
                <th>{{ __('ui.leave_types') }}</th>
                <th>{{ __('ui.from_to') }}</th>
                <th>{{ __('ui.days') }}</th>
                <th>{{ __('ui.reason') }}</th>
                <th>{{ __('ui.actions') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($pending as $a)
            <tr>
                <td><strong>{{ optional($a->staff)->displayName() }}</strong></td>
                <td>{{ optional($a->type)->name }}</td>
                <td>{{ $a->from_date?->format('d M Y') }} — {{ $a->to_date?->format('d M Y') }}</td>
                <td>{{ $a->days }}</td>
                <td>{{ \Illuminate\Support\Str::limit($a->reason, 40) }}</td>
                <td class="actions">
                    <form method="POST" action="{{ route('leave.approvals.review', $a) }}" style="display:inline">
                        @csrf
                        <input type="hidden" name="status" value="approved">
                        <button class="btn btn-sm" type="submit">{{ __('ui.approve') }}</button>
                    </form>
                    <form method="POST" action="{{ route('leave.approvals.review', $a) }}" style="display:inline">
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
@endsection
