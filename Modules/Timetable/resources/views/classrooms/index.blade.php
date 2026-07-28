@extends('layouts.admin')
@section('title', 'Classrooms')

@section('content')
<div class="page-head"><h1>{{ __('ui.classrooms') }}</h1>
    <a href="{{ route('timetable.classrooms.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($classrooms->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.room_no') }}</th><th>{{ __('ui.capacity') }}</th><th>{{ __('ui.status') }}</th><th>{{ __('ui.actions') }}</th></tr></thead>
        <tbody>
        @foreach($classrooms as $c)
            <tr>
                <td><strong>{{ $c->room_no }}</strong></td>
                <td>{{ $c->capacity ?: '—' }}</td>
                <td>{{ $c->is_active ? 'Active' : 'Inactive' }}</td>
                <td class="actions">
                    <a href="{{ route('timetable.classrooms.edit', $c) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('timetable.classrooms.destroy', $c) }}" onsubmit="return confirm('Delete?')">
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
