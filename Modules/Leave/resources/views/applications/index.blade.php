@extends('layouts.admin')
@section('title', 'Leave Applications')

@section('content')
<div class="page-head"><h1>{{ __('ui.leave_applications') }}</h1>
    <a href="{{ route('leave.applications.create') }}" class="btn">+ {{ __('ui.apply_leave') }}</a></div>

<div class="card">
    @if($applications->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>Staff</th>
            <th>Type</th>
            <th>{{ __('ui.from_to') }}</th>
            <th>{{ __('ui.days') }}</th>
            <th>{{ __('ui.status') }}</th>
            <th>{{ __('ui.reason') }}</th>
            <th>{{ __('ui.actions') }}</th>
        </tr></thead>
        <tbody>
        @foreach($applications as $a)
            <tr>
                <td><strong>{{ optional($a->staff)->displayName() }}</strong></td>
                <td>{{ optional($a->type)->name }}</td>
                <td>{{ $a->from_date?->format('d M Y') }} — {{ $a->to_date?->format('d M Y') }}</td>
                <td>{{ $a->days }}</td>
                <td>
                    @if($a->status === \Modules\Leave\Models\LeaveApplication::STATUS_PENDING)
                        <span class="badge">{{ __('ui.pending') }}</span>
                    @elseif($a->status === \Modules\Leave\Models\LeaveApplication::STATUS_APPROVED)
                        <span class="badge">{{ __('ui.approved') }}</span>
                    @else
                        <span class="badge">Rejected</span>
                    @endif
                </td>
                <td>{{ \Illuminate\Support\Str::limit($a->reason, 30) }}</td>
                <td class="actions">
                    <form method="POST" action="{{ route('leave.applications.destroy', $a) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')<button class="btn btn-sm btn-danger">{{ __('ui.delete') }}</button>
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
