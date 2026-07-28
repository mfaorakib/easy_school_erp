@extends('layouts.admin')
@section('title', 'New Group')

@section('content')
<div class="page-head"><h1>{{ __('ui.new_group') }}</h1>
    <a href="{{ route('chat.groups.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>

<div class="card" style="max-width:680px">
    <form method="POST" action="{{ route('chat.groups.store') }}">
        @csrf
        <div class="grid">
            <div><label>{{ __('ui.group_name') }} *</label>
                <input name="name" type="text" maxlength="255" value="{{ old('name') }}" required>
                @error('name')<span class="badge badge-danger">{{ $message }}</span>@enderror
            </div>
            <div><label>Description</label>
                <input name="description" type="text" maxlength="500" value="{{ old('description') }}">
                @error('description')<span class="badge badge-danger">{{ $message }}</span>@enderror
            </div>
            <div><label>{{ __('ui.group_type') }}</label>
                <select name="type">
                    <option value="open" {{ old('type') === 'open' ? 'selected' : '' }}>{{ __('ui.open_group') }}</option>
                    <option value="admin_only" {{ old('type') === 'admin_only' ? 'selected' : '' }}>{{ __('ui.admin_only') }}</option>
                </select>
                @error('type')<span class="badge badge-danger">{{ $message }}</span>@enderror
            </div>
            <div><label>{{ __('ui.classes') }}</label>
                <select name="class_id">
                    <option value="">—</option>
                    @foreach($classes as $c)<option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                </select>
                @error('class_id')<span class="badge badge-danger">{{ $message }}</span>@enderror
            </div>
            <div><label>{{ __('ui.sections') }}</label>
                <select name="section_id">
                    <option value="">—</option>
                    @foreach($sections as $s)<option value="{{ $s->id }}" {{ old('section_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
                </select>
                @error('section_id')<span class="badge badge-danger">{{ $message }}</span>@enderror
            </div>
            <div><label>{{ __('ui.subjects') }}</label>
                <select name="subject_id">
                    <option value="">—</option>
                    @foreach($subjects as $sub)<option value="{{ $sub->id }}" {{ old('subject_id') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>@endforeach
                </select>
                @error('subject_id')<span class="badge badge-danger">{{ $message }}</span>@enderror
            </div>
            <div style="grid-column:1/-1"><label>{{ __('ui.members') }}</label>
                @if($users->isEmpty())
                    <div class="empty">No active users available.</div>
                @else
                    <div style="max-height:220px;overflow:auto">
                        @foreach($users as $u)
                            <label style="display:block"><input type="checkbox" name="members[]" value="{{ $u->id }}" {{ in_array($u->id, old('members', [])) ? 'checked' : '' }}> {{ $u->name }}</label>
                        @endforeach
                    </div>
                @endif
                @error('members')<span class="badge badge-danger">{{ $message }}</span>@enderror
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ __('ui.create') }}</button></div>
    </form>
</div>
@endsection
