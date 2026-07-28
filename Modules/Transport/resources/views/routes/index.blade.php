@extends('layouts.admin')
@section('title', 'Routes')

@section('content')
<div class="page-head"><h1>Routes</h1>
    <a href="{{ route('transport.routes.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($routes->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>Fare</th><th>Route</th><th></th></tr></thead>
        <tbody>
        @foreach($routes as $r)
            <tr>
                <td><strong>{{ $r->name }}</strong></td>
                <td>{{ $r->fare }}</td>
                <td>{{ $r->start_point ?? '—' }} → {{ $r->end_point ?? '—' }}</td>
                <td class="actions">
                    <a href="{{ route('transport.routes.edit', $r) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('transport.routes.destroy', $r) }}" onsubmit="return confirm('Delete?')">
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
