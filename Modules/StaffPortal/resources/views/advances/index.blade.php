@extends('staffportal::layouts.portal')

@section('title', __('ui.salary_advance'))

@section('content')
    <div class="page-head">
        <h1>{{ __('ui.salary_advance') }}</h1>
        <a href="{{ route('staffportal.advances.create') }}" class="btn">+ {{ __('ui.request_advance') }}</a>
    </div>

    <div class="card">
        <div style="display:flex;align-items:baseline;gap:.5rem;">
            <span style="color:var(--muted);font-size:.85rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700;">{{ __('ui.outstanding') }}</span>
            <span style="font-size:1.4rem;font-weight:800;">{{ number_format($outstanding, 2) }}</span>
        </div>
    </div>

    <div class="card">
        @if($advances->isEmpty())
            <div class="empty">{{ __('ui.no_advances') }}</div>
        @else
            <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('ui.amount') }}</th>
                        <th>{{ __('ui.recovered') }}</th>
                        <th>{{ __('ui.status') }}</th>
                        <th>{{ __('ui.reason') }}</th>
                        <th>Review Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($advances as $advance)
                        @php
                            $badgeClass = match($advance->status) {
                                'approved' => 'badge-paid',
                                'rejected' => 'badge-due',
                                default => 'badge-partial',
                            };
                        @endphp
                        <tr>
                            <td>{{ number_format($advance->amount, 2) }}</td>
                            <td>{{ number_format($advance->recovered_amount, 2) }}</td>
                            <td><span class="badge {{ $badgeClass }}">{{ ucfirst($advance->status) }}</span></td>
                            <td>{{ $advance->reason ?: '—' }}</td>
                            <td>{{ $advance->status === 'rejected' ? ($advance->review_note ?: '—') : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
@endsection
