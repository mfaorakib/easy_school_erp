@extends('layouts.admin')
@section('title', 'Certificate Templates')

@section('content')
<div class="page-head"><h1>{{ __('ui.certificate_templates') }}</h1>
    <a href="{{ route('documents.certificateTemplates.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>

<div class="card">
    @if($templates->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>{{ __('ui.name') }}</th>
            <th>{{ __('ui.heading') }}</th>
            <th>{{ __('ui.holder_type') }}</th>
            <th>{{ __('ui.orientation') }}</th>
            <th>{{ __('ui.status') }}</th>
            <th>{{ __('ui.actions') }}</th>
        </tr></thead>
        <tbody>
        @foreach($templates as $t)
            <tr>
                <td><strong>{{ $t->name }}</strong></td>
                <td>{{ $t->heading ?: '—' }}</td>
                <td>
                    <span class="badge">
                        @if($t->holder_type === 'student'){{ __('ui.students') }}
                        @elseif($t->holder_type === 'staff'){{ __('ui.staff') }}
                        @else{{ __('ui.general') }}@endif
                    </span>
                </td>
                <td>{{ $t->orientation === 'landscape' ? __('ui.landscape') : __('ui.portrait') }}</td>
                <td>{{ $t->is_active ? 'Active' : 'Inactive' }}</td>
                <td class="actions">
                    <a href="{{ route('documents.certificateTemplates.edit', $t) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('documents.certificateTemplates.destroy', $t) }}" onsubmit="return confirm('Delete?')">
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
