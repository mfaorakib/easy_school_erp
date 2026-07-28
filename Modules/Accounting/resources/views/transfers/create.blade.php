@extends('layouts.admin')
@section('title', 'Transfer')

@section('content')
<div class="page-head"><h1>{{ __('ui.transfer') }}</h1>
    <a href="{{ route('accounting.transfers.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ route('accounting.transfers.store') }}">
        @csrf
        <div class="grid">
            <div><label>{{ __('ui.from_account') }} *</label>
                <select name="from_bank_account_id" required>
                    <option value="">—</option>
                    @foreach($accounts as $a)<option value="{{ $a->id }}" {{ old('from_bank_account_id') == $a->id ? 'selected' : '' }}>{{ $a->label() }}</option>@endforeach
                </select>
                @error('from_bank_account_id')<span class="badge">{{ $message }}</span>@enderror
            </div>
            <div><label>{{ __('ui.to_account') }} *</label>
                <select name="to_bank_account_id" required>
                    <option value="">—</option>
                    @foreach($accounts as $a)<option value="{{ $a->id }}" {{ old('to_bank_account_id') == $a->id ? 'selected' : '' }}>{{ $a->label() }}</option>@endforeach
                </select>
                @error('to_bank_account_id')<span class="badge">{{ $message }}</span>@enderror
            </div>
            <div><label>{{ __('ui.amount') }} *</label>
                <input name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount') }}" required>
                @error('amount')<span class="badge">{{ $message }}</span>@enderror
            </div>
            <div><label>{{ __('ui.purpose') }}</label>
                <input name="purpose" type="text" maxlength="255" value="{{ old('purpose') }}">
                @error('purpose')<span class="badge">{{ $message }}</span>@enderror
            </div>
            <div><label>Date *</label>
                <input name="transfer_date" type="date" value="{{ old('transfer_date', date('Y-m-d')) }}" required>
                @error('transfer_date')<span class="badge">{{ $message }}</span>@enderror
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ __('ui.transfer') }}</button></div>
    </form>
</div>
@endsection
