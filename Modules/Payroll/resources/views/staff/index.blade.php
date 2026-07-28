@extends('layouts.admin')
@section('title', __('ui.staff_salaries'))

@section('content')
<div class="page-head"><h1>{{ __('ui.staff_salaries') }}</h1>
    <a href="{{ route('payroll.generate.create') }}" class="btn">{{ __('ui.generate_payroll') }}</a></div>

@if($templates->isEmpty())
    <div class="card"><div class="empty">Create a salary template first.</div></div>
@endif

<div class="card">
    @if($staff->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>{{ __('ui.name') }}</th>
            <th>Designation</th>
            <th>Current {{ __('ui.template') }}</th>
            <th>{{ __('ui.assign') }}</th>
        </tr></thead>
        <tbody>
        @foreach($staff as $s)
            <tr>
                <td><strong>{{ $s->displayName() }}</strong></td>
                <td>{{ optional($s->designation)->title ?: '—' }}</td>
                <td>
                    @php($current = optional(optional($assigned->get($s->id))->template)->name)
                    @if($current)<span class="badge">{{ $current }}</span>@else—@endif
                </td>
                <td class="actions">
                    @if($templates->isNotEmpty())
                    <form method="POST" action="{{ route('payroll.staff.assign') }}">
                        @csrf
                        <input type="hidden" name="staff_id" value="{{ $s->id }}">
                        <select name="salary_template_id">
                            @foreach($templates as $t)
                                <option value="{{ $t->id }}" {{ optional($assigned->get($s->id))->salary_template_id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm">{{ __('ui.assign') }}</button>
                    </form>
                    @else
                        —
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
