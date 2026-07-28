@extends('layouts.admin')
@section('title', __('ui.users'))

@section('content')
<div class="page-head">
    <h1>{{ __('ui.users') }}</h1>
</div>

<div class="card">
    <form method="GET" action="{{ route('access.users.index') }}">
        <input name="q" value="{{ $q }}" placeholder="Name, email, username...">
        <button class="btn btn-sm">Search</button>
    </form>
</div>

<div class="card">
    @if($users->isEmpty())
        <div class="empty">{{ __('ui.no_users') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>{{ __('ui.name') }}</th>
                <th>{{ __('ui.email') }}</th>
                <th>{{ __('ui.username') }}</th>
                <th>{{ __('ui.roles') }}</th>
                <th>{{ __('ui.status') }}</th>
                <th>{{ __('ui.actions') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($users as $u)
            <tr>
                <td>{{ $u->name }}</td>
                <td>{{ $u->email }}</td>
                <td>{{ $u->username }}</td>
                <td>
                    @forelse($u->roles as $role)
                        <span class="badge" style="margin-right:.25rem">{{ $role->name }}</span>
                    @empty
                        <span class="text-muted">&mdash;</span>
                    @endforelse
                </td>
                <td>
                    @if($u->is_active)
                        <span class="badge">Active</span>
                    @else
                        <span class="badge">Inactive</span>
                    @endif
                </td>
                <td class="actions">
                    <a href="{{ route('access.users.edit', $u) }}" class="btn btn-sm btn-ghost">{{ __('ui.assign_role') }}</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    <div style="margin-top:1rem">{{ $users->links() }}</div>
    @endif
</div>
@endsection
