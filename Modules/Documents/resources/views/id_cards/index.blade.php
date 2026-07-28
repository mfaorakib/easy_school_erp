@extends('layouts.admin')
@section('title', __('ui.id_cards'))

@section('content')
<div class="page-head"><h1>{{ __('ui.id_cards') }}</h1></div>

<div class="card">
    <p class="badge">{{ __('ui.select_holders') }}</p>
    <form method="GET" action="{{ route('documents.idCards.index') }}">
        <div class="grid">
            <div>
                <label>ID Card Template</label>
                <select name="template_id" onchange="this.form.submit()">
                    <option value="">—</option>
                    @foreach($templates as $t)
                        <option value="{{ $t->id }}" {{ $templateId == $t->id ? 'selected' : '' }}>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if($template && $template->holder_type === 'student')
                <div>
                    <label>{{ __('ui.classes') }}</label>
                    <select name="class_id" onchange="this.form.submit()">
                        <option value="">—</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>{{ __('ui.sections') }}</label>
                    <select name="section_id" onchange="this.form.submit()">
                        <option value="">—</option>
                        @foreach($sections as $sec)
                            <option value="{{ $sec->id }}" {{ $sectionId == $sec->id ? 'selected' : '' }}>{{ $sec->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">Load</button>
            </div>
        </div>
    </form>
</div>

@if(! $template)
    <div class="card">
        <div class="empty">{{ __('ui.select_holders') }}</div>
    </div>
@else
<div class="card">
    <div class="page-head">
        <h1>{{ $template->name }} <span class="badge">{{ $template->holder_type }}</span></h1>
    </div>

    <form method="POST" action="{{ route('documents.idCards.generate') }}" target="_blank">
        @csrf
        <input type="hidden" name="template_id" value="{{ $template->id }}">

        @if($template->holder_type === 'student')
            @if($students->isEmpty())
                <div class="empty">Pick a class/section, or none found.</div>
            @else
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
            @endif
        @else
            @if($staff->isEmpty())
                <div class="empty">{{ __('ui.no_records') }}</div>
            @else
            <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" onclick="this.closest('form').querySelectorAll('input[name=\'holder_ids[]\']').forEach(c=>c.checked=this.checked)"> Select all</th>
                        <th>{{ __('ui.name') }}</th>
                        <th>Staff ID</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($staff as $s)
                    <tr>
                        <td><input type="checkbox" name="holder_ids[]" value="{{ $s->id }}"></td>
                        <td>{{ $s->displayName() }}</td>
                        <td>{{ $s->staff_no }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>
            <div style="margin-top:.5rem">
                <button class="btn btn-sm" type="submit">{{ __('ui.generate_print') }}</button>
            </div>
            @endif
        @endif
    </form>
</div>
@endif
@endsection
