@extends('layouts.admin')
@section('title', $type === 'income' ? __('ui.income') : __('ui.expense'))

@section('content')
<div class="page-head"><h1>{{ $transaction->exists ? __('ui.edit') : __('ui.add') }} — {{ $type === 'income' ? __('ui.income') : __('ui.expense') }}</h1>
    <a href="{{ route('accounting.transactions.index', ['type' => $type]) }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>

<div class="card" style="max-width:680px">
    <form method="POST" enctype="multipart/form-data" action="{{ $transaction->exists ? route('accounting.transactions.update', $transaction) : route('accounting.transactions.store') }}">
        @csrf @if($transaction->exists) @method('PUT') @endif
        <input type="hidden" name="type" value="{{ $type }}">
        <div class="grid">
            <div><label>{{ __('ui.head') }} *</label>
                <select name="account_head_id" required>
                    <option value="">—</option>
                    @foreach($heads as $h)<option value="{{ $h->id }}" {{ old('account_head_id', $transaction->account_head_id) == $h->id ? 'selected' : '' }}>{{ $h->name }}</option>@endforeach
                </select>
                @error('account_head_id')<small class="badge btn-danger">{{ $message }}</small>@enderror
            </div>
            <div><label>Title *</label>
                <input name="title" type="text" maxlength="255" value="{{ old('title', $transaction->title) }}" required>
                @error('title')<small class="badge btn-danger">{{ $message }}</small>@enderror
            </div>
            <div><label>{{ __('ui.amount') }} *</label>
                <input name="amount" type="number" step="0.01" min="0" value="{{ old('amount', $transaction->amount) }}" required>
                @error('amount')<small class="badge btn-danger">{{ $message }}</small>@enderror
            </div>
            <div><label>Date *</label>
                <input name="transaction_date" type="date" value="{{ old('transaction_date', optional($transaction->transaction_date)->format('Y-m-d') ?: date('Y-m-d')) }}" required>
                @error('transaction_date')<small class="badge btn-danger">{{ $message }}</small>@enderror
            </div>
            <div><label>{{ __('ui.payment_method') }}</label>
                <select name="payment_method">
                    @foreach($methods as $m)<option value="{{ $m }}" {{ old('payment_method', $transaction->payment_method) == $m ? 'selected' : '' }}>{{ $m === 'cash' ? __('ui.cash') : ucfirst($m) }}</option>@endforeach
                </select>
                @error('payment_method')<small class="badge btn-danger">{{ $message }}</small>@enderror
            </div>
            <div><label>Account</label>
                <select name="bank_account_id">
                    <option value="">— {{ __('ui.cash') }} —</option>
                    @foreach($banks as $b)<option value="{{ $b->id }}" {{ old('bank_account_id', $transaction->bank_account_id) == $b->id ? 'selected' : '' }}>{{ $b->label() }}</option>@endforeach
                </select>
                @error('bank_account_id')<small class="badge btn-danger">{{ $message }}</small>@enderror
            </div>
            <div><label>{{ __('ui.reference') }}</label>
                <input name="reference" type="text" maxlength="255" value="{{ old('reference', $transaction->reference) }}">
                @error('reference')<small class="badge btn-danger">{{ $message }}</small>@enderror
            </div>
            <div style="grid-column:1/-1"><label>Note</label>
                <textarea name="note" rows="3">{{ old('note', $transaction->note) }}</textarea>
                @error('note')<small class="badge btn-danger">{{ $message }}</small>@enderror
            </div>
            <div style="grid-column:1/-1"><label>Attachment</label>
                <input type="file" name="attachment">
                @if($transaction->exists && $transaction->attachment_path)
                    <a href="{{ asset('storage/'.$transaction->attachment_path) }}" class="btn btn-sm btn-ghost" target="_blank">Current attachment</a>
                @endif
                @error('attachment')<small class="badge btn-danger">{{ $message }}</small>@enderror
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $transaction->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
