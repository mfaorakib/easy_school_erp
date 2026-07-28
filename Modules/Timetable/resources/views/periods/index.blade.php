@extends('layouts.admin')
@section('title', 'Periods')

@section('content')
<div class="page-head"><h1>{{ __('ui.periods') }}</h1>
    <a href="{{ route('timetable.periods.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($periods->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.period_name') }}</th><th>Time</th><th>{{ __('ui.break') }}</th><th>Position</th><th>{{ __('ui.status') }}</th><th>{{ __('ui.actions') }}</th></tr></thead>
        <tbody>
        @foreach($periods as $p)
            <tr>
                <td><strong>{{ $p->name }}</strong></td>
                <td>{{ $p->timeLabel() }}</td>
                <td>{{ $p->is_break ? '' : '—' }}@if($p->is_break)<span class="badge">Yes</span>@endif</td>
                <td>{{ $p->position }}</td>
                <td>{{ $p->is_active ? 'Active' : 'Inactive' }}</td>
                <td class="actions">
                    <a href="{{ route('timetable.periods.edit', $p) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('timetable.periods.destroy', $p) }}" onsubmit="return confirm('Delete?')">
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
