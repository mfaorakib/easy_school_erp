@extends('layouts.admin')
@section('title', 'Room Types')

@section('content')
<div class="page-head"><h1>Room Types</h1>
    <a href="{{ route('dormitory.room-types.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($roomTypes->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($roomTypes as $rt)
            <tr>
                <td><strong>{{ $rt->name }}</strong></td>
                <td class="actions">
                    <a href="{{ route('dormitory.room-types.edit', $rt) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('dormitory.room-types.destroy', $rt) }}" onsubmit="return confirm('Delete?')">
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
