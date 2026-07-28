@extends('layouts.admin')
@section('title', 'Dormitories')

@section('content')
<div class="page-head"><h1>Dormitories</h1>
    <a href="{{ route('dormitory.dormitories.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($dormitories->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>Type</th><th>Address</th><th></th></tr></thead>
        <tbody>
        @foreach($dormitories as $d)
            <tr>
                <td><strong>{{ $d->name }}</strong></td>
                <td><span class="badge">{{ ucfirst($d->type) }}</span></td>
                <td>{{ $d->address ?? '—' }}</td>
                <td class="actions">
                    <a href="{{ route('dormitory.dormitories.edit', $d) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('dormitory.dormitories.destroy', $d) }}" onsubmit="return confirm('Delete?')">
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
