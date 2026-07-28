@extends('layouts.admin')
@section('title', __('ui.staff_attendance'))

@section('content')
<div class="page-head"><h1>{{ __('ui.staff_attendance') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('attendance.staff.index') }}">
        <div class="grid">
            <div><label>{{ __('ui.date') }}</label><input type="date" name="date" value="{{ $date }}"></div>
            <div style="display:flex;align-items:flex-end"><button class="btn">{{ __('ui.load') }}</button></div>
        </div>
    </form>
</div>

<div class="card">
    @if($roster->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <form method="POST" action="{{ route('attendance.staff.store') }}">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">

        <div style="margin-bottom:.75rem">
            <button type="button" class="btn btn-sm btn-ghost"
                onclick="document.querySelectorAll('select[data-att]').forEach(s=>s.value='P')">{{ __('ui.present_all') }}</button>
        </div>

        <div class="overflow-x-auto">
        <table>
            <thead><tr><th>{{ __('ui.name') }}</th><th>Role</th><th>{{ __('ui.status') }}</th><th>Note</th></tr></thead>
            <tbody>
            @foreach($roster as $row)
                <tr>
                    <td>{{ $row['staff']->displayName() }}</td>
                    <td><span class="badge">{{ optional($row['staff']->user)->getRoleNames()->first() ?? '—' }}</span></td>
                    <td>
                        <select name="status[{{ $row['staff']->id }}]" data-att>
                            @foreach($statuses as $st)
                                <option value="{{ $st->value }}" {{ ($row['status'] ?? 'P') === $st->value ? 'selected' : '' }}>{{ $st->label() }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input name="note[{{ $row['staff']->id }}]" value="{{ $row['note'] }}" placeholder="—"></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>

        <div style="margin-top:1.25rem"><button class="btn">{{ __('ui.save') }}</button></div>
    </form>
    @endif
</div>
@endsection
