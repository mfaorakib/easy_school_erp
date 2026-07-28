@extends('layouts.admin')
@section('title', 'Bank Accounts')

@section('content')
<div class="page-head"><h1>{{ __('ui.bank_accounts') }}</h1>
    <a href="{{ route('accounting.banks.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($accounts->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Bank</th><th>Account Name</th><th>Account No</th><th>Type</th><th>{{ __('ui.opening_balance') }}</th><th>Current {{ __('ui.balance') }}</th><th>{{ __('ui.status') }}</th><th>{{ __('ui.actions') }}</th></tr></thead>
        <tbody>
        @foreach($accounts as $account)
            <tr>
                <td><strong>{{ $account->bank_name }}</strong></td>
                <td>{{ $account->account_name }}</td>
                <td>{{ $account->account_number }}</td>
                <td>{{ $account->account_type }}</td>
                <td>{{ number_format($account->opening_balance, 2) }}</td>
                <td><span class="badge">{{ number_format($balances[$account->id], 2) }}</span></td>
                <td><span class="badge">{{ $account->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="actions">
                    <a href="{{ route('accounting.banks.edit', $account) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('accounting.banks.destroy', $account) }}" onsubmit="return confirm('Delete?')">
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
