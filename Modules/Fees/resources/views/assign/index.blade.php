@extends('layouts.admin')
@section('title', __('ui.fee_assign'))

@section('content')
<div class="page-head"><h1>{{ __('ui.fee_assign') }}</h1></div>

<div class="card">
    <h3 style="margin-top:0">Assign a fee to students</h3>
    <p class="empty" style="text-align:left;color:var(--muted);padding:0 0 .75rem">
        Assigns the selected fee to every live student of its class (leave section blank for all sections).
    </p>
    <form method="POST" action="{{ route('fees.assign.store') }}">
        @csrf
        <div class="grid">
            <div><label>Fee</label>
                <select name="fee_master_id" required>
                    <option value="">—</option>
                    @foreach($masters as $m)
                        <option value="{{ $m->id }}">
                            {{ optional($m->type)->name }} · {{ optional($m->schoolClass)->name ?? 'Any' }} · {{ number_format($m->amount, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div><label>{{ __('ui.sections') }} (optional)</label>
                <select name="section_id">
                    <option value="">All sections</option>
                    @foreach($sections as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div style="display:flex;align-items:flex-end"><button class="btn">{{ __('ui.assign') }}</button></div>
        </div>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0">Fees &amp; how many assigned</h3>
    @if($masters->isEmpty())<div class="empty">Create a fee structure first.</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Type</th><th>{{ __('ui.classes') }}</th><th>{{ __('ui.amount') }}</th><th>Assigned</th></tr></thead>
        <tbody>
        @foreach($masters as $m)
            <tr>
                <td><strong>{{ optional($m->type)->name }}</strong></td>
                <td>{{ optional($m->schoolClass)->name ?? 'Any' }}</td>
                <td>{{ number_format($m->amount, 2) }}</td>
                <td><span class="badge">{{ $m->assignments_count }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
