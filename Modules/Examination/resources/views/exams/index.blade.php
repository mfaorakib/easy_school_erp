@extends('layouts.admin')
@section('title', __('ui.exams'))

@section('content')
<div class="page-head"><h1>{{ __('ui.exams') }}</h1>
    <a href="{{ route('exam.exams.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($exams->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>Type</th><th></th></tr></thead>
        <tbody>
        @foreach($exams as $e)
            <tr>
                <td><strong>{{ $e->name }}</strong></td>
                <td>{{ optional($e->type)->name ?? '—' }}</td>
                <td class="actions">
                    <a href="{{ route('exam.exams.edit', $e) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('exam.exams.destroy', $e) }}" onsubmit="return confirm('Delete?')">
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
