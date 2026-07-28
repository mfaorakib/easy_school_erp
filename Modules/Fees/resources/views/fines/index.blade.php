@extends('layouts.admin')
@section('title', __('ui.student_fines'))

@section('content')
<div class="page-head"><h1>{{ __('ui.student_fines') }}</h1></div>

{{-- Filter --}}
<div class="card">
    <form method="GET" action="{{ route('fees.fines.index') }}">
        <div class="grid">
            <div><label>Class</label>
                <select name="class_id" onchange="this.form.submit()">
                    <option value="">—</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>Section (blank = all sections)</label>
                <select name="section_id" onchange="this.form.submit()">
                    <option value="">All sections</option>
                    @foreach($sections as $s)
                        <option value="{{ $s->id }}" {{ $sectionId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>Status</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="" {{ $status === null ? 'selected' : '' }}>All</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="collected" {{ $status === 'collected' ? 'selected' : '' }}>Collected</option>
                </select>
            </div>
            <div style="display:flex;align-items:flex-end">
                <a href="{{ route('fees.fines.index') }}" class="btn btn-sm btn-ghost">Reset</a>
            </div>
        </div>
    </form>
</div>

{{-- Impose a fine --}}
<div class="card">
    <h3 style="margin-top:0">Impose a fine</h3>
    <form method="POST" action="{{ route('fees.fines.store') }}">
        @csrf
        <div class="grid">
            <div><label>Student</label>
                <select name="student_id" required>
                    <option value="">—</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}">
                            {{ $s->full_name }}@if($s->records->first()) · Roll {{ $s->records->first()->roll_no }}@endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div><label>Reason</label>
                <input type="text" name="reason" required>
            </div>
            <div><label>Amount</label>
                <input type="number" name="amount" step="0.01" min="0.01" required>
            </div>
            <div><label>Fine date</label>
                <input type="date" name="fine_date" value="{{ now()->toDateString() }}">
            </div>
            <div style="display:flex;align-items:flex-end">
                <button class="btn" type="submit">Impose Fine</button>
            </div>
        </div>
    </form>
</div>

{{-- Fines list --}}
<div class="card">
    <h3 style="margin-top:0">Fines</h3>
    @if($fines->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Reason</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($fines as $fine)
            <tr>
                <td>{{ $fine->studentLabel() }}</td>
                <td>{{ $fine->reason }}</td>
                <td>{{ number_format($fine->amount, 2) }}</td>
                <td>{{ $fine->fine_date->format('d M Y') }}</td>
                <td>
                    @if($fine->is_collected)
                        <span class="badge">Collected</span>
                    @else
                        <span class="badge" style="color:var(--danger)">Pending</span>
                    @endif
                </td>
                <td>
                    @if($fine->is_collected)
                        <a href="{{ route('fees.fines.receipt', $fine) }}" class="btn btn-sm">Receipt</a>
                    @else
                        <form method="POST" action="{{ route('fees.fines.collect', $fine) }}" style="display:inline">
                            @csrf
                            <button class="btn btn-sm" type="submit">Collect</button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
