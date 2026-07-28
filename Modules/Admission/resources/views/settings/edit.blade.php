@extends('layouts.admin')
@section('title', 'Admission Settings')

@section('content')
<div class="page-head"><h1>{{ __('ui.settings') }} — {{ __('ui.id_format') }}</h1></div>

<div class="card max-w-full" style="max-width:640px">
    <p>This pattern controls the unique ID that gets generated automatically for every newly admitted student. You can build your own pattern using these placeholders:</p>
    <ul>
        <li><code>{{ '{YYYY}' }}</code> — the full 4-digit year, e.g. 2026</li>
        <li><code>{{ '{YY}' }}</code> — just the last 2 digits of the year, e.g. 26</li>
        <li><code>{{ '{SEQ:N}' }}</code> — an auto-incrementing number, padded with leading zeros to N digits (e.g. <code>{{ '{SEQ:4}' }}</code> becomes 0007 for the 7th student)</li>
    </ul>

    <form method="POST" action="{{ route('admission.admin.settings.update') }}">
        @csrf @method('PUT')
        <div class="grid">
            <div style="grid-column:1/-1">
                <label>{{ __('ui.id_format') }}</label>
                <input name="id_format" type="text" maxlength="60" value="{{ old('id_format', $pattern) }}" required>
                @error('id_format')<div class="badge btn-danger">{{ $message }}</div>@enderror
            </div>
            <div style="grid-column:1/-1">
                <p>{{ __('ui.preview') }}: <strong>{{ $example }}</strong></p>
            </div>
            <div style="grid-column:1/-1">
                <button type="button" class="btn btn-sm btn-ghost" onclick="document.querySelector('[name=id_format]').value='STU-{YYYY}-{SEQ:4}'">STU-{{ '{YYYY}' }}-{{ '{SEQ:4}' }}</button>
                <button type="button" class="btn btn-sm btn-ghost" onclick="document.querySelector('[name=id_format]').value='{YY}{SEQ:5}'">{{ '{YY}' }}{{ '{SEQ:5}' }}</button>
                <button type="button" class="btn btn-sm btn-ghost" onclick="document.querySelector('[name=id_format]').value='STU{SEQ:6}'">STU{{ '{SEQ:6}' }}</button>
                <button type="button" class="btn btn-sm btn-ghost" onclick="document.querySelector('[name=id_format]').value='STU-{YY}-{SEQ:4}'">STU-{{ '{YY}' }}-{{ '{SEQ:4}' }}</button>
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ __('ui.save') }}</button></div>
    </form>
</div>
@endsection
