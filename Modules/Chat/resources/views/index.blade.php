@extends('layouts.admin')
@section('title', __('ui.messages'))

@section('content')
<div class="page-head">
    <h1>{{ __('ui.messages') }}</h1>
    <a href="{{ route('chat.contacts') }}" class="btn">+ {{ __('ui.contacts') }}</a>
</div>

<div class="card" style="min-width:260px">
    @if($threads->isEmpty())
        <div class="empty">
            {{ __('ui.no_messages') }}
            <div style="margin-top:.5rem">
                <a href="{{ route('chat.contacts') }}" class="btn btn-sm btn-ghost">{{ __('ui.start_chat') }}</a>
            </div>
        </div>
    @else
        @foreach($threads as $thread)
            <a href="{{ route('chat.show', $thread['user']) }}"
               style="display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:.6rem .25rem;border-bottom:1px solid rgba(0,0,0,.06);text-decoration:none">
                <span style="min-width:0">
                    <strong style="display:block">{{ $thread['user']->name }}</strong>
                    <span class="text-muted" style="display:block;font-size:.85em;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        @if($thread['last']->message_type === 'file')
                            <i class="fa-solid fa-paperclip" aria-hidden="true"></i> {{ $thread['last']->attachment_name }}
                        @else
                            {{ \Illuminate\Support\Str::limit($thread['last']->body, 40) }}
                        @endif
                    </span>
                </span>
                <span style="text-align:right;white-space:nowrap">
                    <span class="text-muted" style="display:block;font-size:.75em">{{ $thread['last']->created_at->diffForHumans() }}</span>
                    @if($thread['unread'] > 0)
                        <span class="badge">{{ $thread['unread'] }}</span>
                    @endif
                </span>
            </a>
        @endforeach
    @endif
</div>
@endsection
