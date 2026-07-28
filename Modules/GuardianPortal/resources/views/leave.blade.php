@extends('guardianportal::layouts.portal')

@section('title', __('ui.my_leave_requests'))

@section('content')
    <div class="page-head">
        <h1>{{ __('ui.my_leave_requests') }}</h1>
        <a href="{{ route('portal.children.show', $student->id) }}" class="btn btn-ghost">&larr; {{ $student->full_name }}</a>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('portal.children.leave.store', $student->id) }}">
            @csrf

            <div style="margin-bottom:1rem;">
                <label for="leave_type_id" style="display:block;font-weight:600;margin-bottom:.4rem;">{{ __('ui.leave_types') }}</label>
                <select
                    id="leave_type_id"
                    name="leave_type_id"
                    required
                    style="width:100%;padding:.6rem .75rem;border:1px solid var(--border);border-radius:8px;background:var(--surface-2);color:var(--text);"
                >
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" @selected(old('leave_type_id') == $type->id)>
                            {{ $type->name }} ({{ $type->days_allowed }} {{ __('ui.days_allowed') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
                <div style="flex:1;min-width:160px;">
                    <label for="from_date" style="display:block;font-weight:600;margin-bottom:.4rem;">{{ __('ui.from_date') }}</label>
                    <input
                        type="date"
                        id="from_date"
                        name="from_date"
                        value="{{ old('from_date') }}"
                        required
                        style="width:100%;padding:.6rem .75rem;border:1px solid var(--border);border-radius:8px;background:var(--surface-2);color:var(--text);"
                    >
                </div>
                <div style="flex:1;min-width:160px;">
                    <label for="to_date" style="display:block;font-weight:600;margin-bottom:.4rem;">{{ __('ui.to_date') }}</label>
                    <input
                        type="date"
                        id="to_date"
                        name="to_date"
                        value="{{ old('to_date') }}"
                        required
                        style="width:100%;padding:.6rem .75rem;border:1px solid var(--border);border-radius:8px;background:var(--surface-2);color:var(--text);"
                    >
                </div>
            </div>

            <div style="margin-bottom:1rem;">
                <label for="reason" style="display:block;font-weight:600;margin-bottom:.4rem;">{{ __('ui.reason') }}</label>
                <textarea
                    id="reason"
                    name="reason"
                    rows="3"
                    maxlength="1000"
                    style="width:100%;padding:.6rem .75rem;border:1px solid var(--border);border-radius:8px;background:var(--surface-2);color:var(--text);font-family:inherit;"
                >{{ old('reason') }}</textarea>
            </div>

            <button type="submit" class="btn">{{ __('ui.request_leave') }}</button>
        </form>
    </div>

    <div class="card">
        <div class="page-head">
            <h1 style="font-size:1.1rem">{{ __('ui.my_leave_requests') }}</h1>
        </div>

        @if($requests->isEmpty())
            <div class="empty">{{ __('ui.no_leave_requests') }}</div>
        @else
            <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('ui.leave_types') }}</th>
                        <th>{{ __('ui.from_date') }}</th>
                        <th>{{ __('ui.to_date') }}</th>
                        <th>{{ __('ui.days') }}</th>
                        <th>{{ __('ui.status') }}</th>
                        <th>Review Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                        @php
                            $badgeClass = match($req->status) {
                                'approved' => 'badge-paid',
                                'rejected' => 'badge-due',
                                default => 'badge-partial',
                            };
                        @endphp
                        <tr>
                            <td>{{ optional($req->type)->name }}</td>
                            <td>{{ $req->from_date?->format('d M Y') }}</td>
                            <td>{{ $req->to_date?->format('d M Y') }}</td>
                            <td>{{ $req->days }}</td>
                            <td><span class="badge {{ $badgeClass }}">{{ ucfirst($req->status) }}</span></td>
                            <td>{{ $req->review_note ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
@endsection
