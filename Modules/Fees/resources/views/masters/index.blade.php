@extends('layouts.admin')
@section('title', __('ui.fee_masters'))

@section('content')
<div class="page-head"><h1>{{ __('ui.fee_masters') }}</h1>
    <a href="{{ route('fees.masters.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($masters->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Group</th><th>Type</th><th>{{ __('ui.classes') }}</th><th>{{ __('ui.amount') }}</th><th>Due date</th><th>Fine</th><th></th></tr></thead>
        <tbody>
        @foreach($masters as $m)
            <tr>
                <td>{{ optional($m->group)->name }}</td>
                <td><strong>{{ optional($m->type)->name }}</strong></td>
                <td>{{ optional($m->schoolClass)->name ?? 'Any' }}</td>
                <td>{{ number_format($m->amount, 2) }}</td>
                <td>{{ optional($m->due_date)->format('d M Y') ?? '—' }}</td>
                <td>{{ $m->fine_type === 'none' ? '—' : ($m->fine_type === 'percentage' ? $m->fine_value.'%' : $m->fine_value) }}</td>
                <td class="actions">
                    <a href="{{ route('fees.masters.edit', $m) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('fees.masters.destroy', $m) }}" onsubmit="return confirm('Delete?')">
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
