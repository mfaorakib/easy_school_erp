@extends('layouts.admin')
@section('title', 'Leave Types')

@section('content')
<div class="page-head"><h1>{{ __('ui.leave_types') }}</h1>
    <a href="{{ route('leave.types.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>

<div class="card">
    @if($types->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>{{ __('ui.days_allowed') }}</th><th>{{ __('ui.status') }}</th><th>{{ __('ui.actions') }}</th></tr></thead>
        <tbody>
        @foreach($types as $t)
            <tr>
                <td><strong>{{ $t->name }}</strong></td>
                <td><span class="badge">{{ $t->days_allowed }}</span></td>
                <td><span class="badge">{{ $t->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="actions">
                    <a href="{{ route('leave.types.edit', $t) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('leave.types.destroy', $t) }}" onsubmit="return confirm('Delete?')">
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
