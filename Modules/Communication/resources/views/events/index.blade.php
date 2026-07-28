@extends('layouts.admin')
@section('title', 'Events')

@section('content')
<div class="page-head"><h1>Events</h1>
    <a href="{{ route('communication.events.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($events->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Title</th><th>Start</th><th>End</th><th>Location</th><th></th></tr></thead>
        <tbody>
        @foreach($events as $event)
            <tr>
                <td><strong>{{ $event->title }}</strong></td>
                <td>{{ $event->start_date?->format('d M Y') }}</td>
                <td>{{ $event->end_date?->format('d M Y') ?? '—' }}</td>
                <td>{{ $event->location }}</td>
                <td class="actions">
                    <a href="{{ route('communication.events.edit', $event) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('communication.events.destroy', $event) }}" onsubmit="return confirm('Delete?')">
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
