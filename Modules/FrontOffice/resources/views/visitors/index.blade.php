@extends('layouts.admin')
@section('title', 'Visitors')

@section('content')
<div class="page-head"><h1>{{ __('ui.visitors') }}</h1>
    <a href="{{ route('frontoffice.visitors.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($visitors->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>{{ __('ui.name') }}</th>
            <th>{{ __('ui.mobile') }}</th>
            <th>{{ __('ui.purpose') }}</th>
            <th>{{ __('ui.to_meet') }}</th>
            <th>{{ __('ui.visit_date') }}</th>
            <th>In</th>
            <th>Out</th>
            <th>{{ __('ui.status') }}</th>
            <th></th>
        </tr></thead>
        <tbody>
        @foreach($visitors as $v)
            <tr>
                <td><strong>{{ $v->name }}</strong></td>
                <td>{{ $v->phone ?: '—' }}</td>
                <td>{{ $v->purpose ?: '—' }}</td>
                <td>{{ $v->to_meet ?: '—' }}</td>
                <td>{{ $v->visit_date?->format('d M Y') }}</td>
                <td>{{ substr($v->in_time, 0, 5) ?: '—' }}</td>
                <td>{{ substr($v->out_time, 0, 5) ?: '—' }}</td>
                <td><span class="badge">{{ $v->isCheckedOut() ? __('ui.checked_out') : __('ui.in_premises') }}</span></td>
                <td class="actions">
                    @if(! $v->isCheckedOut())
                    <form method="POST" action="{{ route('frontoffice.visitors.checkout', $v) }}">
                        @csrf<button class="btn btn-sm btn-ghost">{{ __('ui.check_out') }}</button>
                    </form>
                    @endif
                    <a href="{{ route('frontoffice.visitors.edit', $v) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('frontoffice.visitors.destroy', $v) }}" onsubmit="return confirm('Delete?')">
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
