@extends('layouts.admin')
@section('title', 'Call Log')

@section('content')
<div class="page-head"><h1>{{ __('ui.call_log') }}</h1>
    <a href="{{ route('frontoffice.callLogs.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($logs->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>{{ __('ui.name') }}</th>
            <th>{{ __('ui.mobile') }}</th>
            <th>{{ __('ui.call_type') }}</th>
            <th>Date</th>
            <th>Duration</th>
            <th>{{ __('ui.next_follow_up') }}</th>
            <th>{{ __('ui.actions') }}</th>
        </tr></thead>
        <tbody>
        @foreach($logs as $l)
            <tr>
                <td><strong>{{ $l->name }}</strong></td>
                <td>{{ $l->phone }}</td>
                <td><span class="badge">{{ $l->call_type === \Modules\FrontOffice\Models\PhoneCallLog::TYPE_INCOMING ? __('ui.incoming') : __('ui.outgoing') }}</span></td>
                <td>{{ $l->call_date?->format('d M Y') }}</td>
                <td>{{ $l->call_duration ?: '—' }}</td>
                <td>{{ $l->next_follow_up_date?->format('d M Y') ?: '—' }}</td>
                <td class="actions">
                    <a href="{{ route('frontoffice.callLogs.edit', $l) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('frontoffice.callLogs.destroy', $l) }}" onsubmit="return confirm('Delete?')">
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
