@extends('layouts.admin')
@section('title', __('ui.fee_types'))

@section('content')
<div class="page-head"><h1>{{ __('ui.fee_types') }}</h1>
    <a href="{{ route('fees.types.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($types->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>Code</th><th></th></tr></thead>
        <tbody>
        @foreach($types as $t)
            <tr>
                <td><strong>{{ $t->name }}</strong></td>
                <td>{{ $t->code ?? '—' }}</td>
                <td class="actions">
                    <a href="{{ route('fees.types.edit', $t) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('fees.types.destroy', $t) }}" onsubmit="return confirm('Delete?')">
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
