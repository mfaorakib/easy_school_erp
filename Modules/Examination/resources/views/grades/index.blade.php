@extends('layouts.admin')
@section('title', __('ui.grades'))

@section('content')
<div class="page-head"><h1>{{ __('ui.grades') }}</h1>
    <a href="{{ route('exam.grades.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($grades->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.grade') }}</th><th>{{ __('ui.gpa') }}</th><th>Marks range</th><th></th></tr></thead>
        <tbody>
        @foreach($grades as $g)
            <tr>
                <td><strong>{{ $g->name }}</strong></td>
                <td>{{ number_format($g->gpa, 2) }}</td>
                <td>{{ (int) $g->mark_from }} – {{ (int) $g->mark_upto }}</td>
                <td class="actions">
                    <a href="{{ route('exam.grades.edit', $g) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('exam.grades.destroy', $g) }}" onsubmit="return confirm('Delete?')">
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
