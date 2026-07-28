@extends('layouts.admin')
@section('title', 'Complaint Types')

@section('content')
<div class="page-head"><h1>{{ __('ui.complaint_types') }}</h1>
    <a href="{{ route('frontoffice.complaintTypes.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($types->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>{{ __('ui.status') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($types as $type)
            <tr>
                <td><strong>{{ $type->name }}</strong></td>
                <td>
                    @if($type->is_active)<span class="badge">{{ __('ui.active') }}</span>
                    @else<span class="badge">{{ __('ui.inactive') }}</span>@endif
                </td>
                <td class="actions">
                    <a href="{{ route('frontoffice.complaintTypes.edit', $type) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('frontoffice.complaintTypes.destroy', $type) }}" onsubmit="return confirm('Delete?')">
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
