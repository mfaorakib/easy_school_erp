@extends('layouts.admin')
@section('title', __('ui.contacts'))

@section('content')
<div class="page-head"><h1>{{ __('ui.contacts') }}</h1>
    <a href="{{ route('chat.index') }}" class="btn btn-ghost">{{ __('ui.back') }} {{ __('ui.messages') }}</a></div>

<div class="card">
    <form method="GET" action="{{ route('chat.contacts') }}" class="actions">
        <input name="q" value="{{ $search }}" placeholder="Search name or email">
        <button class="btn btn-sm">Search</button>
    </form>
</div>

<div class="card">
    @if($users->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>Email</th><th>Role</th><th>{{ __('ui.actions') }}</th></tr></thead>
        <tbody>
        @foreach($users as $u)
            <tr>
                <td><strong>{{ $u->name }}</strong></td>
                <td>{{ $u->email }}</td>
                <td><small class="badge">{{ $u->getRoleNames()->implode(', ') ?: '—' }}</small></td>
                <td class="actions">
                    @if(!in_array($u->id, $blockedIds))
                        <a href="{{ route('chat.show', $u) }}" class="btn btn-sm">{{ __('ui.start_chat') }}</a>
                        <form method="POST" action="{{ route('chat.block', $u) }}">
                            @csrf<button class="btn btn-sm btn-ghost">{{ __('ui.block') }}</button>
                        </form>
                    @else
                        <span class="badge">{{ __('ui.blocked') }}</span>
                        <form method="POST" action="{{ route('chat.unblock', $u) }}">
                            @csrf @method('DELETE')<button class="btn btn-sm">{{ __('ui.unblock') }}</button>
                        </form>
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
