@extends('layouts.admin')
@section('title', 'ID Card Templates')

@section('content')
<div class="page-head"><h1>{{ __('ui.id_card_templates') }}</h1>
    <a href="{{ route('documents.idCardTemplates.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>

<div class="card">
    @if($templates->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>{{ __('ui.name') }}</th>
            <th>{{ __('ui.holder_type') }}</th>
            <th>{{ __('ui.orientation') }}</th>
            <th>{{ __('ui.background_color') }}</th>
            <th>{{ __('ui.status') }}</th>
            <th></th>
        </tr></thead>
        <tbody>
        @foreach($templates as $t)
            <tr>
                <td><strong>{{ $t->name }}</strong></td>
                <td><span class="badge">{{ $t->holder_type === 'student' ? __('ui.students') : __('ui.staff') }}</span></td>
                <td>{{ $t->orientation === 'portrait' ? __('ui.portrait') : __('ui.landscape') }}</td>
                <td><span style="display:inline-block;width:18px;height:18px;border-radius:4px;background:{{ $t->background_color }}"></span></td>
                <td><span class="badge">{{ $t->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="actions">
                    <a href="{{ route('documents.idCardTemplates.edit', $t) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('documents.idCardTemplates.destroy', $t) }}" onsubmit="return confirm('Delete?')">
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
