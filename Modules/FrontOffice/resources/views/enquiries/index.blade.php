@extends('layouts.admin')
@section('title', 'Enquiries')

@section('content')
<div class="page-head"><h1>{{ __('ui.enquiries') }}</h1>
    <a href="{{ route('frontoffice.enquiries.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>

<div class="card">
    @if($enquiries->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>{{ __('ui.name') }}</th>
            <th>{{ __('ui.mobile') }}</th>
            <th>{{ __('ui.classes') }}</th>
            <th>Enquiry Date</th>
            <th>{{ __('ui.next_follow_up') }}</th>
            <th>{{ __('ui.status') }}</th>
            <th>{{ __('ui.actions') }}</th>
        </tr></thead>
        <tbody>
        @foreach($enquiries as $e)
            <tr>
                <td><strong>{{ $e->name }}</strong></td>
                <td>{{ $e->phone }}</td>
                <td>{{ optional($e->schoolClass)->name ?: '—' }}</td>
                <td>{{ $e->enquiry_date?->format('d M Y') }}</td>
                <td>{{ $e->next_follow_up_date?->format('d M Y') ?: '—' }}</td>
                <td>
                    @if($e->status === 'won')
                        <span class="badge">{{ __('ui.won') }}</span>
                    @elseif($e->status === 'lost')
                        <span class="badge">{{ __('ui.lost') }}</span>
                    @else
                        <span class="badge">{{ __('ui.active_lead') }}</span>
                    @endif
                </td>
                <td class="actions">
                    <a href="{{ route('frontoffice.enquiries.show', $e) }}" class="btn btn-sm btn-ghost">View</a>
                    <a href="{{ route('frontoffice.enquiries.edit', $e) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('frontoffice.enquiries.destroy', $e) }}" onsubmit="return confirm('Delete?')">
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
