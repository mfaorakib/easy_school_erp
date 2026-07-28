@extends('layouts.admin')
@section('title', __('ui.permission_matrix').' — '.$role->name)

@section('content')
<div class="page-head">
    <div>
        <h1>{{ __('ui.permission_matrix') }} — {{ $role->name }}</h1>
        @if(in_array($role->name, ['super-admin','student','parent','teacher','admin','accountant','receptionist','librarian','driver']))
            <p style="margin:.25rem 0 0;color:#64748b;font-size:.85rem">{{ __('ui.system_role_note') }}</p>
        @endif
    </div>
    <a href="{{ route('access.roles.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a>
</div>

<form method="POST" action="{{ route('access.roles.update', $role) }}">
    @csrf
    @method('PUT')

    <div class="card" style="display:flex;gap:.5rem;align-items:center">
        <button type="button" class="btn btn-sm btn-ghost" onclick="document.querySelectorAll('.perm-cb').forEach(c=>c.checked=true)">{{ __('ui.select_all') }}</button>
        <button type="button" class="btn btn-sm btn-ghost" onclick="document.querySelectorAll('.perm-cb').forEach(c=>c.checked=false)">{{ __('ui.clear_all') }}</button>
    </div>

    @foreach($groups as $groupLabel => $permissions)
        <div class="card">
            <h3 style="margin:0 0 .6rem;font-size:1rem">{{ $groupLabel }}</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:.2rem .8rem">
                @foreach($permissions as $key => $description)
                    <label style="display:flex;align-items:flex-start;gap:.5rem;padding:.4rem 0;font-size:.85rem;font-weight:400">
                        <input type="checkbox" class="perm-cb" name="permissions[{{ $key }}]" value="1" {{ in_array($key, $current) ? 'checked' : '' }} style="margin-top:.15rem">
                        <span><strong style="font-family:monospace;font-size:.78rem">{{ $key }}</strong><br><span style="color:#64748b">{{ $description }}</span></span>
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="card">
        <button type="submit" class="btn">{{ __('ui.save') }}</button>
    </div>
</form>
@endsection
