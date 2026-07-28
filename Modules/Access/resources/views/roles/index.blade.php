@extends('layouts.admin')
@section('title', __('ui.roles'))

@section('content')
<div class="page-head">
    <h1>{{ __('ui.roles') }}</h1>
    <a href="{{ route('access.roles.create') }}" class="btn">+ {{ __('ui.new_role') }}</a>
</div>

<div class="card">
    @if($roles->isEmpty())
        <div class="empty">{{ __('ui.no_roles') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>{{ __('ui.role_name') }}</th>
                <th>{{ __('ui.permissions') }}</th>
                <th>{{ __('ui.assigned_users') }}</th>
                <th></th>
                <th>{{ __('ui.actions') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($roles as $role)
            @php $isSystemRole = in_array($role->name, ['super-admin','student','parent','teacher','admin','accountant','receptionist','librarian','driver']); @endphp
            <tr>
                <td><strong>{{ $role->name }}</strong></td>
                <td><span class="badge">{{ $role->permissions_count }}</span></td>
                <td><span class="badge">{{ $role->users_count }}</span></td>
                <td>@if($isSystemRole)<span class="badge">{{ __('ui.system_role') }}</span>@endif</td>
                <td class="actions">
                    <a href="{{ route('access.roles.edit', $role) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    @if(!$isSystemRole)
                        <form method="POST" action="{{ route('access.roles.destroy', $role) }}" onsubmit="return confirm('{{ __('ui.delete') }}?')">
                            @csrf @method('DELETE')<button class="btn btn-sm btn-danger">{{ __('ui.delete') }}</button>
                        </form>
                    @else
                        <span style="font-size:.75rem;color:#94a3b8">{{ __('ui.system_role_note') }}</span>
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
