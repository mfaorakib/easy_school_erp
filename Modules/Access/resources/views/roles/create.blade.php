@extends('layouts.admin')
@section('title', __('ui.new_role'))

@section('content')
<div class="page-head">
    <h1>{{ __('ui.new_role') }}</h1>
    <a href="{{ route('access.roles.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a>
</div>

<div class="card" style="max-width:500px">
    <form method="POST" action="{{ route('access.roles.store') }}">
        @csrf
        <label>{{ __('ui.role_name') }} *</label>
        <input name="name" value="{{ old('name') }}" required>
        @error('name')<div style="color:#dc2626;font-size:.85rem;margin-top:.25rem">{{ $message }}</div>@enderror

        <p style="font-size:.85rem;color:#94a3b8;margin-top:.75rem">{{ __('ui.permissions') }} are set on the next screen after creating the role.</p>

        <div style="margin-top:1.25rem"><button class="btn">{{ __('ui.create') }}</button></div>
    </form>
</div>
@endsection
