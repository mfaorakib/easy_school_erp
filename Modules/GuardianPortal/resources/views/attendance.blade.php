@extends('guardianportal::layouts.portal')

@section('title', __('ui.attendance_history'))

@section('content')
    <div class="page-head">
        <h1>{{ __('ui.attendance_history') }}</h1>
        <a href="{{ route('portal.children.show', $student->id) }}" class="btn btn-ghost">&larr; {{ $student->full_name }}</a>
    </div>

    <div class="card">
        <div style="color:var(--muted);font-size:.9rem">
            {{ $history['present'] }} / {{ $history['total'] }}
            &middot; {{ __('ui.attendance_rate') }}: <strong style="color:var(--text)">{{ $history['percent'] }}%</strong>
        </div>
    </div>

    <div class="card">
        @if($history['total'] === 0)
            <div class="empty">No attendance has been recorded for this student yet.</div>
        @else
            <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('ui.date') }}</th>
                        <th>{{ __('ui.status') }}</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history['rows'] as $row)
                        <tr>
                            <td>{{ $row->date->format('Y-m-d') }}</td>
                            <td>{{ $row->status?->label() }}</td>
                            <td>{{ $row->note ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
@endsection
