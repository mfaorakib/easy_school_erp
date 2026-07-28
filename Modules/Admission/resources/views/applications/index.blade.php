@extends('layouts.admin')
@section('title', 'Admission Applications')

@section('content')
<div class="page-head"><h1>{{ __('ui.admission_applications') }}</h1></div>

<div class="actions flex-wrap" style="margin-bottom:1rem">
    <a href="{{ route('admission.admin.applications.index') }}" class="btn btn-sm {{ $status ? 'btn-ghost' : '' }}">All</a>
    <a href="{{ route('admission.admin.applications.index', ['status' => 'pending']) }}" class="btn btn-sm {{ $status === 'pending' ? '' : 'btn-ghost' }}">{{ __('ui.pending') }}</a>
    <a href="{{ route('admission.admin.applications.index', ['status' => 'confirmed']) }}" class="btn btn-sm {{ $status === 'confirmed' ? '' : 'btn-ghost' }}">{{ __('ui.confirmed') }}</a>
    <a href="{{ route('admission.admin.applications.index', ['status' => 'rejected']) }}" class="btn btn-sm {{ $status === 'rejected' ? '' : 'btn-ghost' }}">{{ __('ui.rejected') }}</a>
</div>

<div class="card">
    @if($applications->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead>
        <tr>
            <th>{{ __('ui.reference_no') }}</th>
            <th>{{ __('ui.name') }}</th>
            <th>{{ __('ui.desired_class') }}</th>
            <th>{{ __('ui.status') }}</th>
            <th>{{ __('ui.date') }}</th>
            <th>{{ __('ui.actions') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($applications as $a)
            <tr>
                <td><code>{{ $a->reference_no }}</code></td>
                <td>{{ $a->first_name }} {{ $a->last_name }}</td>
                <td>{{ $a->desiredClass->name ?? '—' }}</td>
                <td>
                    @if($a->status === 'pending')
                        <span class="badge" style="background:#fef3c7;color:#92400e">{{ __('ui.pending') }}</span>
                    @elseif($a->status === 'confirmed')
                        <span class="badge" style="background:#dcfce7;color:#166534">{{ __('ui.confirmed') }}</span>
                    @else
                        <span class="badge" style="background:#fee2e2;color:#991b1b">{{ __('ui.rejected') }}</span>
                    @endif
                </td>
                <td>{{ $a->created_at->format('d M Y') }}</td>
                <td class="actions">
                    <a href="{{ route('admission.admin.applications.show', $a) }}" class="btn btn-sm btn-ghost">View</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    {{ $applications->links() }}
    @endif
</div>
@endsection
