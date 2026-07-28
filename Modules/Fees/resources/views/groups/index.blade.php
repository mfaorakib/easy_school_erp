@extends('layouts.admin')
@section('title', __('ui.fee_groups'))

@section('content')
<div class="page-head"><h1>{{ __('ui.fee_groups') }}</h1>
    <a href="{{ route('fees.groups.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($groups->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>Description</th><th></th></tr></thead>
        <tbody>
        @foreach($groups as $g)
            <tr>
                <td><strong>{{ $g->name }}</strong></td>
                <td>{{ $g->description ?? '—' }}</td>
                <td class="actions">
                    <a href="{{ route('fees.groups.edit', $g) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('fees.groups.destroy', $g) }}" onsubmit="return confirm('Delete?')">
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
