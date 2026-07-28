@extends('layouts.admin')
@section('title', __('ui.payment_methods'))

@section('content')
<div class="page-head"><h1>{{ __('ui.payment_methods') }}</h1>
    <a href="{{ route('settings.paymentMethods.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>

<div class="card">
    @if($methods->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>{{ __('ui.gateway_type') }}</th><th>Position</th><th>{{ __('ui.status') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($methods as $m)
            <tr>
                <td><strong>{{ $m->name }}</strong></td>
                <td><span class="badge">{{ $m->driver === 'manual' ? __('ui.manual') : ucfirst($m->driver) }}</span></td>
                <td>{{ $m->position }}</td>
                <td>
                    @if($m->is_active)
                        <span class="badge">Active</span>
                    @else
                        <span class="badge">Inactive</span>
                    @endif
                </td>
                <td class="actions">
                    <a href="{{ route('settings.paymentMethods.edit', $m) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('settings.paymentMethods.destroy', $m) }}" onsubmit="return confirm('Delete?')">
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
