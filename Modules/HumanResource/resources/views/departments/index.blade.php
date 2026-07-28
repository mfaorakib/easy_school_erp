@extends('layouts.admin')
@section('title', __('ui.departments'))

@section('content')
<div class="page-head"><h1>{{ __('ui.departments') }}</h1>
    <a href="{{ route('hr.departments.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>

<div class="card">
    @if($departments->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($departments as $d)
            <tr>
                <td><strong>{{ $d->name }}</strong></td>
                <td class="actions">
                    <a href="{{ route('hr.departments.edit', $d) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('hr.departments.destroy', $d) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">{{ __('ui.delete') }}</button>
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
