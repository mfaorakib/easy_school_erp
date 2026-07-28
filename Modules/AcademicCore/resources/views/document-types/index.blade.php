@extends('layouts.admin')
@section('title', 'Document Types')

@section('content')
<div class="page-head"><h1>{{ __('ui.document_types') }}</h1>
    <a href="{{ route('academic.document-types.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>

<div class="card">
    @if($documentTypes->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>{{ __('ui.required') }}</th><th>{{ __('ui.status') }}</th><th>Sort Order</th><th>{{ __('ui.actions') }}</th></tr></thead>
        <tbody>
        @foreach($documentTypes as $documentType)
            <tr>
                <td><strong>{{ $documentType->name }}</strong></td>
                <td><span class="badge">{{ $documentType->is_required ? __('ui.required') : __('ui.optional') }}</span></td>
                <td><span class="badge">{{ $documentType->is_active ? __('ui.active') : __('ui.inactive') }}</span></td>
                <td>{{ $documentType->sort_order }}</td>
                <td class="actions">
                    <a href="{{ route('academic.document-types.edit', $documentType) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('academic.document-types.destroy', $documentType) }}" onsubmit="return confirm('Delete?')">
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
