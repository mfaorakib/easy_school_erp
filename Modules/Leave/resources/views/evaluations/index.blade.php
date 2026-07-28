@extends('layouts.admin')
@section('title', 'Evaluations')

@section('content')
<div class="page-head"><h1>{{ __('ui.evaluations') }}</h1>
    <a href="{{ route('leave.evaluations.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>

<div class="card">
    @if($evaluations->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Staff</th><th>{{ __('ui.term') }}</th><th>Date</th><th>{{ __('ui.total_score') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($evaluations as $e)
            <tr>
                <td><strong>{{ optional($e->staff)->displayName() }}</strong></td>
                <td>{{ $e->term ?: '—' }}</td>
                <td>{{ $e->evaluation_date?->format('d M Y') }}</td>
                <td><span class="badge">{{ number_format($e->total_score, 2) }}</span></td>
                <td class="actions">
                    <a href="{{ route('leave.evaluations.edit', $e) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('leave.evaluations.destroy', $e) }}" onsubmit="return confirm('Delete?')">
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
