@extends('layouts.admin')
@section('title', 'Salary Templates')

@section('content')
<div class="page-head"><h1>{{ $template->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.salary_templates') }}</h1>
    <a href="{{ route('payroll.templates.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>

<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $template->exists ? route('payroll.templates.update', $template) : route('payroll.templates.store') }}">
        @csrf @if($template->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.name') }} *</label><input name="name" type="text" maxlength="150" value="{{ old('name', $template->name) }}" required></div>
            <div><label>{{ __('ui.basic_salary') }} *</label><input name="basic_salary" type="number" step="0.01" value="{{ old('basic_salary', $template->basic_salary ?? 0) }}" required></div>
            <div style="align-self:end"><label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->exists ? $template->is_active : true) ? 'checked' : '' }}> Active</label></div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $template->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>

@if($template->exists)
<div class="card">
    <div class="page-head"><h1>{{ __('ui.component') }}s</h1></div>

    <div class="actions" style="margin-bottom:1rem">
        <span class="badge">{{ __('ui.basic_salary') }}: {{ number_format($totals['basic'], 2) }}</span>
        <span class="badge">{{ __('ui.earnings') }}: {{ number_format($totals['earnings'], 2) }}</span>
        <span class="badge">{{ __('ui.deductions') }}: {{ number_format($totals['deductions'], 2) }}</span>
        <span class="badge">{{ __('ui.gross') }}: {{ number_format($totals['gross'], 2) }}</span>
        <span class="badge">{{ __('ui.net_pay') }}: {{ number_format($totals['net'], 2) }}</span>
    </div>

    @if($template->components->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>{{ __('ui.name') }}</th>
            <th>Type</th>
            <th>Calc</th>
            <th>Value</th>
            <th>Resolved</th>
            <th>{{ __('ui.actions') }}</th>
        </tr></thead>
        <tbody>
        @foreach($template->components as $c)
            <tr>
                <td>{{ $c->name }}</td>
                <td><span class="badge">{{ $c->type === 'earning' ? __('ui.earning') : __('ui.deduction') }}</span></td>
                <td>{{ $c->calc_type === 'percent' ? __('ui.percent') : __('ui.fixed') }}</td>
                <td>{{ $c->calc_type === 'percent' ? $c->value.'%' : number_format($c->value, 2) }}</td>
                <td>{{ number_format($c->resolve($template->basic_salary), 2) }}</td>
                <td>
                    <form method="POST" action="{{ route('payroll.templates.components.remove', $c) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')<button class="btn btn-sm btn-danger">{{ __('ui.delete') }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif

    <div style="margin-top:1.25rem">
        <h3>{{ __('ui.add_component') }}</h3>
        <form method="POST" action="{{ route('payroll.templates.components.add', $template) }}">
            @csrf
            <div class="grid">
                <div><label>{{ __('ui.name') }}</label><input name="name" type="text" maxlength="150" required></div>
                <div><label>Type</label>
                    <select name="type">
                        <option value="earning">{{ __('ui.earning') }}</option>
                        <option value="deduction">{{ __('ui.deduction') }}</option>
                    </select>
                </div>
                <div><label>Calc</label>
                    <select name="calc_type">
                        <option value="fixed">{{ __('ui.fixed') }}</option>
                        <option value="percent">{{ __('ui.percent') }}</option>
                    </select>
                </div>
                <div><label>Value</label><input name="value" type="number" step="0.01" required></div>
                <div style="align-self:end"><button class="btn btn-sm" type="submit">{{ __('ui.add_component') }}</button></div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
