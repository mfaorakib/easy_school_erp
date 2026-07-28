@extends('layouts.admin')
@section('title', 'Complaints')

@section('content')
<div class="page-head"><h1>{{ __('ui.complaints') }}</h1>
    <a href="{{ route('frontoffice.complaints.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($complaints->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>Complainant</th>
            <th>{{ __('ui.mobile') }}</th>
            <th>Type</th>
            <th>Date</th>
            <th>{{ __('ui.assigned_to') }}</th>
            <th>{{ __('ui.status') }}</th>
            <th></th>
        </tr></thead>
        <tbody>
        @foreach($complaints as $c)
            <tr>
                <td><strong>{{ $c->complainant_name }}</strong></td>
                <td>{{ $c->phone }}</td>
                <td>{{ optional($c->type)->name ?: '—' }}</td>
                <td>{{ $c->complaint_date?->format('d M Y') }}</td>
                <td>{{ optional($c->assignee)->displayName() ?: '—' }}</td>
                <td>
                    @if($c->status === 'open')<span class="badge">{{ __('ui.open') }}</span>
                    @elseif($c->status === 'in_progress')<span class="badge">{{ __('ui.in_progress') }}</span>
                    @elseif($c->status === 'resolved')<span class="badge">{{ __('ui.resolved') }}</span>@endif
                </td>
                <td class="actions">
                    <a href="{{ route('frontoffice.complaints.edit', $c) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('frontoffice.complaints.destroy', $c) }}" onsubmit="return confirm('Delete?')">
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
