@extends('layouts.admin')
@section('title', 'Holidays')

@section('content')
<div class="page-head"><h1>{{ __('ui.holidays') }}</h1>
    <a href="{{ route('settings.holidays.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($holidays->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>{{ __('ui.from_to') }}</th><th>Days</th><th>Description</th><th></th></tr></thead>
        <tbody>
        @foreach($holidays as $h)
            <tr>
                <td><strong>{{ $h->title }}</strong></td>
                <td>{{ $h->from_date?->format('d M Y') }} — {{ $h->to_date?->format('d M Y') }}</td>
                <td><span class="badge">{{ $h->days() }}</span></td>
                <td>{{ $h->description ?: '—' }}</td>
                <td class="actions">
                    <a href="{{ route('settings.holidays.edit', $h) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('settings.holidays.destroy', $h) }}" onsubmit="return confirm('Delete?')">
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
