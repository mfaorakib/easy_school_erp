@extends('layouts.admin')
@section('title', 'Postal')

@section('content')
<div class="page-head">
    <h1>{{ $type === 'receive' ? __('ui.receive') : ($type === 'dispatch' ? __('ui.dispatch') : __('ui.postal')) }}</h1>
    <a href="{{ route('frontoffice.postal.create', ['type' => $type ?: 'dispatch']) }}" class="btn">+ {{ __('ui.add') }}</a>
</div>

<div class="card">
    <div class="actions" style="margin-bottom:1rem">
        <a href="{{ route('frontoffice.postal.index') }}" class="btn btn-sm btn-ghost {{ $type ? '' : 'btn-danger' }}">All</a>
        <a href="{{ route('frontoffice.postal.index', ['type' => 'dispatch']) }}" class="btn btn-sm btn-ghost {{ $type === 'dispatch' ? 'btn-danger' : '' }}">{{ __('ui.dispatch') }}</a>
        <a href="{{ route('frontoffice.postal.index', ['type' => 'receive']) }}" class="btn btn-sm btn-ghost {{ $type === 'receive' ? 'btn-danger' : '' }}">{{ __('ui.receive') }}</a>
    </div>

    @if($records->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Title</th>
                <th>To / From</th>
                <th>Reference No</th>
                <th>Date</th>
                <th>Attachment</th>
                <th>{{ __('ui.actions') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($records as $r)
            <tr>
                <td><span class="badge">{{ $r->type === 'receive' ? __('ui.receive') : __('ui.dispatch') }}</span></td>
                <td><strong>{{ $r->title }}</strong></td>
                <td>{{ $r->party ?: '—' }}</td>
                <td>{{ $r->reference_no ?: '—' }}</td>
                <td>{{ $r->postal_date?->format('d M Y') }}</td>
                <td>
                    @if($r->attachment_path)
                        <a href="{{ asset('storage/'.$r->attachment_path) }}">file</a>
                    @else
                        —
                    @endif
                </td>
                <td class="actions">
                    <a href="{{ route('frontoffice.postal.edit', $r) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('frontoffice.postal.destroy', $r) }}" onsubmit="return confirm('Delete?')" style="display:inline">
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
