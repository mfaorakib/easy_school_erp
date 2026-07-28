@extends('guardianportal::layouts.portal')

@section('title', $student->full_name)

@section('content')
    @php
        $record = $student->liveRecord()->first();
        $className = optional(optional($record)->schoolClass)->name;
        $sectionName = optional(optional($record)->section)->name;
    @endphp

    <div class="page-head">
        <h1>{{ $student->full_name }}</h1>
        <a href="{{ route('portal.dashboard') }}" class="btn btn-ghost">&larr; {{ __('ui.my_children') }}</a>
    </div>

    <div class="card">
        <div style="color:var(--muted);font-size:.9rem">
            {{ __('ui.class') }}: <strong style="color:var(--text)">{{ $className ?? '—' }}</strong>
            @if($sectionName)
                &middot; {{ __('ui.section') }}: <strong style="color:var(--text)">{{ $sectionName }}</strong>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="page-head">
            <h1 style="font-size:1.1rem">{{ __('ui.fee_summary') }}</h1>
        </div>

        @if(empty($ledger))
            <div class="empty">No fees have been assigned to this student yet.</div>
        @else
            <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('ui.name') }}</th>
                        <th>{{ __('ui.amount') }}</th>
                        <th>{{ __('ui.discount') }}</th>
                        <th>{{ __('ui.paid') }}</th>
                        <th>{{ __('ui.due') }}</th>
                        <th>{{ __('ui.status') }}</th>
                        <th>{{ __('ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ledger as $row)
                        @php
                            $balance = $row['balance'];
                            $badgeClass = match($balance['status']) {
                                'paid' => 'badge-paid',
                                'partial' => 'badge-partial',
                                default => 'badge-due',
                            };
                        @endphp
                        <tr>
                            <td>{{ $row['assignment']->master->type->name ?? '—' }}</td>
                            <td>{{ number_format($balance['amount'], 2) }}</td>
                            <td>{{ number_format($balance['discount'], 2) }}</td>
                            <td>{{ number_format($balance['paid'], 2) }}</td>
                            <td>{{ number_format($balance['due'], 2) }}</td>
                            <td><span class="badge {{ $badgeClass }}">{{ ucfirst($balance['status']) }}</span></td>
                            <td>
                                @if($balance['due'] > 0)
                                    <form method="POST" action="{{ route('portal.pay.start') }}" style="display:flex;gap:.4rem;align-items:center;flex-wrap:wrap">
                                        @csrf
                                        <input type="hidden" name="fee_assignment_id" value="{{ $row['assignment']->id }}">
                                        <input type="hidden" name="amount" value="{{ $balance['due'] }}">
                                        <select name="payment_method_id" style="border:1px solid var(--border);border-radius:8px;padding:.35rem .5rem;background:var(--surface);color:var(--text);font-size:.82rem">
                                            @foreach($paymentMethods as $method)
                                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm">{{ __('ui.pay_now') }}</button>
                                    </form>
                                @else
                                    <span class="badge badge-paid">&#10003;</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>

    <div class="card" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
        <div style="color:var(--muted);font-size:.9rem">{{ __('ui.attendance_history') }}</div>
        <a href="{{ route('portal.children.attendance', $student->id) }}" class="btn btn-ghost">{{ __('ui.view') }}</a>
    </div>

    <div class="card" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
        <div style="color:var(--muted);font-size:.9rem">{{ __('ui.my_leave_requests') }}</div>
        <a href="{{ route('portal.children.leave.index', $student->id) }}" class="btn btn-ghost">{{ __('ui.request_leave') }}</a>
    </div>

    <div class="card" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
        <div style="color:var(--muted);font-size:.9rem">{{ __('ui.student_documents') }}</div>
        <a href="{{ route('portal.children.documents', $student->id) }}" class="btn btn-ghost">{{ __('ui.view') }}</a>
    </div>
@endsection
