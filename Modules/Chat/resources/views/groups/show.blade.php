@extends('layouts.admin')
@section('title', $group->name)

@section('content')
<div class="page-head">
    <h1>{{ __('ui.groups') }}</h1>
    <a href="{{ route('chat.groups.index') }}" class="btn btn-sm btn-ghost">{{ __('ui.back') }}</a>
</div>

<div class="grid" style="grid-template-columns: 280px 1fr; gap:16px; align-items:start;">
    {{-- Left pane: groups list --}}
    <div class="card">
        @if($groups->isEmpty())
            <div class="empty">{{ __('ui.no_messages') }}</div>
        @else
            @foreach($groups as $row)
                <a href="{{ route('chat.groups.show', $row['group']) }}"
                   class="btn btn-sm btn-ghost"
                   style="display:block; text-align:left; {{ $row['group']->id === $group->id ? 'font-weight:bold; background:rgba(0,0,0,.06);' : '' }}">
                    {{ $row['group']->name }}
                    @if($row['unread'] > 0)
                        <span class="badge">{{ $row['unread'] }}</span>
                    @endif
                </a>
            @endforeach
        @endif
    </div>

    {{-- Right pane: the thread --}}
    <div class="card">
        <div class="page-head">
            <h1>
                {{ $group->name }}
                <span class="badge">{{ $group->isAdminOnly() ? __('ui.admin_only') : __('ui.open_group') }}</span>
            </h1>
            <a href="{{ route('chat.groups.members', $group) }}" class="btn btn-sm btn-ghost">{{ __('ui.members') }}</a>
        </div>

        @if(optional($group->schoolClass)->name || optional($group->section)->name || optional($group->subject)->name)
            <div style="margin-bottom:12px;">
                @if(optional($group->schoolClass)->name)
                    <span class="badge">{{ $group->schoolClass->name }}</span>
                @endif
                @if(optional($group->section)->name)
                    <span class="badge">{{ $group->section->name }}</span>
                @endif
                @if(optional($group->subject)->name)
                    <span class="badge">{{ $group->subject->name }}</span>
                @endif
            </div>
        @endif

        {{-- Messages --}}
        <div style="margin-bottom:16px;">
            @forelse($messages as $m)
                @php $mine = $m->sender_id === auth()->id(); @endphp
                <div style="margin-bottom:10px; {{ $mine ? 'text-align:right;' : '' }}">
                    <div class="muted" style="font-size:.85em;">
                        {{ $mine ? __('ui.you') : optional($m->sender)->name }}
                        · {{ $m->created_at->format('d M H:i') }}
                    </div>
                    @if($m->body)
                        <div>{{ $m->body }}</div>
                    @endif
                    @if($m->message_type === 'file' && $m->attachment_path)
                        <div>
                            <a href="{{ asset('storage/'.$m->attachment_path) }}"><i class="fa-solid fa-paperclip" aria-hidden="true"></i> {{ $m->attachment_name }}</a>
                        </div>
                    @endif
                </div>
            @empty
                <div class="empty">{{ __('ui.no_messages') }}</div>
            @endforelse
        </div>

        {{-- Composer --}}
        @if($canPost)
            <form method="POST" action="{{ route('chat.groups.send', $group) }}" enctype="multipart/form-data">
                @csrf
                <div>
                    <textarea name="body" placeholder="{{ __('ui.type_a_message') }}">{{ old('body') }}</textarea>
                    @error('body') <div class="badge">{{ $message }}</div> @enderror
                    @error('group') <div class="badge">{{ $message }}</div> @enderror
                </div>
                <div class="grid" style="grid-template-columns: 1fr auto; gap:8px; align-items:center;">
                    <input type="file" name="attachment">
                    <button class="btn btn-sm" type="submit">{{ __('ui.send') }}</button>
                </div>
            </form>
        @else
            <p class="muted">Only admins can post in this group.</p>
        @endif
    </div>
</div>
@endsection
