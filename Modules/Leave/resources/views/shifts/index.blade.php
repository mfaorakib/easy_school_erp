@extends('layouts.admin')
@section('title', 'Shifts')

@section('content')
<div class="page-head"><h1>{{ __('ui.shifts') }}</h1>
    <div class="actions">
        <a href="{{ route('leave.shifts.assign') }}" class="btn btn-ghost">{{ __('ui.assign_shifts') }}</a>
        <a href="{{ route('leave.shifts.create') }}" class="btn">+ {{ __('ui.add') }}</a>
    </div>
</div>

<div class="card">
    @if($shifts->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>Time</th><th>{{ __('ui.status') }}</th><th>{{ __('ui.actions') }}</th></tr></thead>
        <tbody>
        @foreach($shifts as $s)
            <tr>
                <td><strong>{{ $s->name }}</strong></td>
                <td>{{ $s->timeLabel() }}</td>
                <td>
                    @if($s->is_active)
                        <span class="badge">Active</span>
                    @else
                        <span class="badge">Inactive</span>
                    @endif
                </td>
                <td class="actions">
                    <a href="{{ route('leave.shifts.edit', $s) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('leave.shifts.destroy', $s) }}" onsubmit="return confirm('Delete?')" style="display:inline">
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
