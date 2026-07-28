@extends('staffportal::layouts.portal')

@section('title', __('ui.resignation'))

@section('content')
    @php($canApply = $applications->where('status', 'pending')->isEmpty())

    <div class="page-head">
        <h1>{{ __('ui.resignation') }}</h1>
        @if($canApply)
            <a href="{{ route('staffportal.resignation.create') }}" class="btn">+ {{ __('ui.submit_resignation') }}</a>
        @endif
    </div>

    <div class="card">
        @if($applications->isEmpty())
            <div class="empty">{{ __('ui.no_resignations') }}</div>
        @else
            <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('ui.intended_last_day') }}</th>
                        <th>{{ __('ui.status') }}</th>
                        <th>{{ __('ui.reason') }}</th>
                        <th>Review Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $application)
                        @php
                            $badgeClass = match($application->status) {
                                'approved' => 'badge-paid',
                                'rejected' => 'badge-due',
                                default => 'badge-partial',
                            };
                        @endphp
                        <tr>
                            <td>{{ $application->intended_last_day?->format('d M Y') }}</td>
                            <td><span class="badge {{ $badgeClass }}">{{ ucfirst($application->status) }}</span></td>
                            <td>{{ $application->reason ?: '—' }}</td>
                            <td>{{ $application->status === 'rejected' ? ($application->review_note ?: '—') : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
@endsection
