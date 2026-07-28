@extends('layouts.admin')
@section('title', 'Salary Templates')

@section('content')
<div class="page-head"><h1>{{ __('ui.salary_templates') }}</h1>
    <a href="{{ route('payroll.templates.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>

<div class="card">
    @if($templates->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>{{ __('ui.name') }}</th>
            <th>{{ __('ui.basic_salary') }}</th>
            <th>Components</th>
            <th>{{ __('ui.gross') }}</th>
            <th>{{ __('ui.net_pay') }}</th>
            <th>{{ __('ui.status') }}</th>
            <th>{{ __('ui.actions') }}</th>
        </tr></thead>
        <tbody>
        @foreach($templates as $t)
            <tr>
                <td><strong>{{ $t->name }}</strong></td>
                <td>{{ number_format($t->basic_salary, 2) }}</td>
                <td><span class="badge">{{ $t->components_count }}</span></td>
                <td>{{ number_format($totals[$t->id]['gross'], 2) }}</td>
                <td><span class="badge">{{ number_format($totals[$t->id]['net'], 2) }}</span></td>
                <td>{{ $t->is_active ? 'Active' : 'Inactive' }}</td>
                <td class="actions">
                    <a href="{{ route('payroll.templates.edit', $t) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('payroll.templates.destroy', $t) }}" onsubmit="return confirm('Delete?')">
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
