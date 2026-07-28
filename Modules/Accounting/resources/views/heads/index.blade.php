@extends('layouts.admin')
@section('title', 'Account Heads')

@section('content')
<div class="page-head"><h1>{{ __('ui.account_heads') }}</h1>
    <a href="{{ route('accounting.heads.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($heads->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>Type</th><th>Description</th><th>{{ __('ui.status') }}</th><th>{{ __('ui.actions') }}</th></tr></thead>
        <tbody>
        @foreach($heads as $head)
            <tr>
                <td><strong>{{ $head->name }}</strong></td>
                <td><span class="badge">{{ $head->type === 'income' ? __('ui.income') : __('ui.expense') }}</span></td>
                <td>{{ \Illuminate\Support\Str::limit($head->description, 40) }}</td>
                <td><span class="badge">{{ $head->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="actions">
                    <a href="{{ route('accounting.heads.edit', $head) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('accounting.heads.destroy', $head) }}" onsubmit="return confirm('Delete?')">
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
