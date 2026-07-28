@extends('layouts.admin')
@section('title', 'Homework')

@section('content')
<div class="page-head"><h1>Homework</h1>
    <a href="{{ route('homework.homeworks.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($homeworks->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>{{ __('ui.classes') }}</th><th>{{ __('ui.sections') }}</th><th>{{ __('ui.subjects') }}</th><th>Homework date</th><th>Submission</th><th></th></tr></thead>
        <tbody>
        @foreach($homeworks as $h)
            <tr>
                <td><strong>{{ $h->title }}</strong></td>
                <td>{{ optional($h->schoolClass)->name }}</td>
                <td>{{ optional($h->section)->name }}</td>
                <td>{{ optional($h->subject)->name }}</td>
                <td>{{ optional($h->homework_date)->format('d M Y') }}</td>
                <td>{{ optional($h->submission_date)->format('d M Y') }}</td>
                <td class="actions">
                    <a href="{{ route('homework.homeworks.edit', $h) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('homework.homeworks.destroy', $h) }}" onsubmit="return confirm('Delete?')">
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
