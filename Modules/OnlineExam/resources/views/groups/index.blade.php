@extends('layouts.admin')
@section('title', 'Question Groups')

@section('content')
<div class="page-head"><h1>{{ __('ui.question_groups') }}</h1>
    <a href="{{ route('onlineexam.groups.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>

<div class="card">
    @if($groups->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th># Questions</th><th>{{ __('ui.status') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($groups as $group)
            <tr>
                <td><strong>{{ $group->title }}</strong></td>
                <td><span class="badge">{{ $group->questions_count }}</span></td>
                <td>
                    @if($group->is_active)<span class="badge">Active</span>@else<span class="badge">Inactive</span>@endif
                </td>
                <td class="actions">
                    <a href="{{ route('onlineexam.groups.edit', $group) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('onlineexam.groups.destroy', $group) }}" onsubmit="return confirm('Delete?')">
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
