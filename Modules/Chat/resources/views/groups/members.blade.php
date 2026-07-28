@extends('layouts.admin')
@section('title', 'Group Members')

@section('content')
<div class="page-head"><h1>{{ $group->name }} — {{ __('ui.members') }}</h1>
    <a href="{{ route('chat.groups.show', $group) }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>

<div class="card">
    @if($group->members->isEmpty())
        <div class="empty">No members yet.</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>{{ __('ui.name') }}</th>
                <th>Email</th>
                <th>Role</th>
                @if($isAdmin)<th>{{ __('ui.actions') }}</th>@endif
            </tr>
        </thead>
        <tbody>
        @foreach($group->members as $m)
            <tr>
                <td>{{ optional($m->user)->name }}</td>
                <td>{{ optional($m->user)->email }}</td>
                <td><span class="badge">{{ $m->isAdmin() ? __('ui.admin') : __('ui.member') }}</span></td>
                @if($isAdmin)
                <td>
                    <div class="actions">
                        <form method="POST" action="{{ route('chat.groups.members.role', [$group, $m->user_id]) }}" style="display:inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="role" value="{{ $m->isAdmin() ? 'member' : 'admin' }}">
                            <button class="btn btn-sm" type="submit">{{ $m->isAdmin() ? __('ui.make_member') : __('ui.make_admin') }}</button>
                        </form>
                        <form method="POST" action="{{ route('chat.groups.members.remove', [$group, $m->user_id]) }}" style="display:inline" onsubmit="return confirm('{{ __('ui.remove') }}?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" type="submit">{{ __('ui.remove') }}</button>
                        </form>
                    </div>
                </td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>

@if($isAdmin)
<div class="card">
    <div class="page-head"><h1>{{ __('ui.add_member') }}</h1></div>
    @if($candidates->isEmpty())
        <div class="empty">All active users are already members.</div>
    @else
    <form method="POST" action="{{ route('chat.groups.members.add', $group) }}">
        @csrf
        <div class="grid">
            <div>
                <label>{{ __('ui.name') }}</label>
                <select name="user_id" required>
                    <option value="">—</option>
                    @foreach($candidates as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">{{ __('ui.add_member') }}</button>
            </div>
        </div>
    </form>
    @endif
</div>
@endif
@endsection
