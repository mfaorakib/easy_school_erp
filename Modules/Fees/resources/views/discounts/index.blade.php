@extends('layouts.admin')
@section('title', __('ui.fee_discounts'))

@section('content')
<div class="page-head"><h1>{{ __('ui.fee_discounts') }}</h1>
    <a href="{{ route('fees.discounts.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($discounts->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>Type</th><th>{{ __('ui.amount') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($discounts as $d)
            <tr>
                <td><strong>{{ $d->name }}</strong></td>
                <td><span class="badge">{{ ucfirst($d->amount_type) }}</span></td>
                <td>{{ $d->amount_type === 'percentage' ? $d->amount.'%' : $d->amount }}</td>
                <td class="actions">
                    <a href="{{ route('fees.discounts.edit', $d) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('fees.discounts.destroy', $d) }}" onsubmit="return confirm('Delete?')">
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
