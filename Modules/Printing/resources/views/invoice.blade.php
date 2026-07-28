@extends('layouts.admin')
@section('title', __('ui.fee_statement'))

@section('content')
<div class="page-head"><h1>{{ __('ui.fee_statement') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('printing.invoice') }}">
        <div class="grid">
            <div>
                <label>{{ __('ui.classes') }}</label>
                <select name="class_id">
                    <option value="">—</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>{{ __('ui.sections') }}</label>
                <select name="section_id">
                    <option value="">—</option>
                    @foreach($sections as $sec)
                        <option value="{{ $sec->id }}" {{ $sectionId == $sec->id ? 'selected' : '' }}>{{ $sec->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">Load</button>
            </div>
        </div>
    </form>
</div>

@if($students->isNotEmpty())
<div class="card">
    <form method="POST" action="{{ route('printing.invoice.generate') }}" target="_blank">
        @csrf
        <div class="overflow-x-auto">
        <table>
            <thead>
                <tr>
                    <th><input type="checkbox" onclick="this.closest('form').querySelectorAll('input[name=\'holder_ids[]\']').forEach(c=>c.checked=this.checked)"> Select all</th>
                    <th>{{ __('ui.name') }}</th>
                    <th>Admission</th>
                </tr>
            </thead>
            <tbody>
            @foreach($students as $s)
                <tr>
                    <td><input type="checkbox" name="holder_ids[]" value="{{ $s->id }}"></td>
                    <td>{{ $s->full_name }}</td>
                    <td>{{ $s->admission_no }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <div style="margin-top:.5rem">
            <button class="btn btn-sm" type="submit">{{ __('ui.generate_print') }}</button>
        </div>
    </form>
</div>
@else
<div class="card">
    <div class="empty">{{ __('ui.select_class_section') }}</div>
</div>
@endif
@endsection
