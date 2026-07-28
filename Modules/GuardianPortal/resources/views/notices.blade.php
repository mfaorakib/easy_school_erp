@extends('guardianportal::layouts.portal')

@section('title', __('ui.notices'))

@section('content')
    <div class="page-head">
        <h1>{{ __('ui.notices') }}</h1>
    </div>

    <div class="card">
        @if($notices->isEmpty())
            <div class="empty">{{ __('ui.no_notices_yet') }}</div>
        @else
            <div style="display:flex;flex-direction:column;gap:1rem">
                @foreach($notices as $notice)
                    <div style="padding-bottom:1rem;border-bottom:1px solid var(--border)">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:.6rem;flex-wrap:wrap">
                            <h3 style="margin:0;font-size:1rem">{{ $notice->title }}</h3>
                            <span style="color:var(--muted);font-size:.8rem;white-space:nowrap">{{ $notice->notice_date?->format('d M, Y') }}</span>
                        </div>
                        @if($notice->description)
                            <p style="margin:.5rem 0 0;color:var(--muted);font-size:.9rem">{{ $notice->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card">
        <div class="page-head">
            <h1 style="font-size:1.1rem">{{ __('ui.events') }}</h1>
        </div>

        @if($events->isEmpty())
            <div class="empty">{{ __('ui.no_upcoming_events') }}</div>
        @else
            <div style="display:flex;flex-direction:column;gap:.85rem">
                @foreach($events as $event)
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:.6rem;flex-wrap:wrap;padding-bottom:.85rem;border-bottom:1px solid var(--border)">
                        <h3 style="margin:0;font-size:.95rem">{{ $event->title }}</h3>
                        <span style="color:var(--muted);font-size:.8rem;white-space:nowrap">{{ $event->start_date?->format('d M, Y') }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
