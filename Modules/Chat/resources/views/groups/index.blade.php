@extends('layouts.admin')
@section('title', __('ui.groups'))

@section('content')
<div class="page-head">
    <h1>{{ __('ui.groups') }}</h1>
    <a href="{{ route('chat.groups.create') }}" class="btn">+ {{ __('ui.new_group') }}</a>
</div>

<div class="card">
    @if($groups->isEmpty())
        <div class="empty">
            {{ __('ui.no_messages') }}
            <p><a href="{{ route('chat.groups.create') }}" class="btn btn-sm">+ {{ __('ui.new_group') }}</a></p>
        </div>
    @else
        <div class="overflow-x-auto">
        <table>
            <tbody>
            @foreach($groups as $row)
                <tr>
                    <td>
                        <a href="{{ route('chat.groups.show', $row['group']) }}">
                            <strong>{{ $row['group']->name }}</strong>
                        </a>
                        <span class="badge">{{ $row['group']->isAdminOnly() ? __('ui.admin_only') : __('ui.open_group') }}</span>
                    </td>
                    <td class="muted">{{ $row['last'] ? \Illuminate\Support\Str::limit($row['last']->body, 40) : '—' }}</td>
                    <td>
                        @if($row['unread'] > 0)
                            <span class="badge">{{ $row['unread'] }} {{ __('ui.unread') }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>
@endsection
