@extends('layouts.admin')
@section('title', 'Notice Board')

@section('content')
<div class="page-head"><h1>Notice Board</h1></div>

<div class="grid">
    <div class="card">
        <h3 style="margin-top:0">Notices</h3>
        @if($notices->isEmpty())
            <div class="empty">{{ __('ui.no_records') }}</div>
        @else
            @foreach($notices as $notice)
                <div style="padding:.75rem 0;border-bottom:1px solid var(--border)">
                    <strong>{{ $notice->title }}</strong>
                    <div class="l" style="color:var(--muted);font-size:.85rem;margin:.15rem 0 .4rem">
                        {{ $notice->notice_date->format('d M Y') }}
                    </div>
                    @foreach(($notice->audiences ?? []) as $audience)
                        <span class="badge">{{ $audience }}</span>
                    @endforeach
                    @if($notice->description)
                        <p style="margin:.5rem 0 0">{{ $notice->description }}</p>
                    @endif
                </div>
            @endforeach
        @endif
    </div>

    <div class="card">
        <h3 style="margin-top:0">Upcoming Events</h3>
        @if($events->isEmpty())
            <div class="empty">{{ __('ui.no_records') }}</div>
        @else
            <div class="overflow-x-auto">
            <table>
                <thead><tr><th>Title</th><th>Start</th><th>End</th><th>Location</th></tr></thead>
                <tbody>
                @foreach($events as $event)
                    <tr>
                        <td><strong>{{ $event->title }}</strong></td>
                        <td>{{ $event->start_date->format('d M Y') }}</td>
                        <td>{{ optional($event->end_date)->format('d M Y') ?? '—' }}</td>
                        <td>{{ $event->location ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
</div>
@endsection
