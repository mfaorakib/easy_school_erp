@extends('layouts.admin')
@section('title', 'Bank Accounts')

@section('content')
<div class="page-head"><h1>{{ $account->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.bank_accounts') }}</h1>
    <a href="{{ route('accounting.banks.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $account->exists ? route('accounting.banks.update', $account) : route('accounting.banks.store') }}">
        @csrf @if($account->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>Bank name *</label><input name="bank_name" type="text" maxlength="255" value="{{ old('bank_name', $account->bank_name) }}" required></div>
            <div><label>Account name</label><input name="account_name" type="text" maxlength="255" value="{{ old('account_name', $account->account_name) }}"></div>
            <div><label>Account number</label><input name="account_number" type="text" maxlength="255" value="{{ old('account_number', $account->account_number) }}"></div>
            <div><label>Account type</label><input name="account_type" type="text" maxlength="255" value="{{ old('account_type', $account->account_type) }}"></div>
            <div><label>{{ __('ui.opening_balance') }}</label><input name="opening_balance" type="number" step="0.01" value="{{ old('opening_balance', $account->exists ? $account->opening_balance : 0) }}"></div>
            <div style="grid-column:1/-1"><label>Note</label><textarea name="note" rows="4">{{ old('note', $account->note) }}</textarea></div>
            <div><label>{{ __('ui.status') }}</label>
                <label style="font-weight:normal"><input name="is_active" type="checkbox" value="1" {{ old('is_active', $account->exists ? $account->is_active : true) ? 'checked' : '' }}> Active</label>
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $account->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
