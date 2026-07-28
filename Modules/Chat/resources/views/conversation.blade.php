@extends('layouts.admin')
@section('title', $user->name)

@section('content')
<div class="page-head">
    <h1>{{ __('ui.messages') }}</h1>
    <a href="{{ route('chat.index') }}" class="btn btn-sm btn-ghost">{{ __('ui.back') }}</a>
</div>

<div style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-start">

    {{-- Thread list --}}
    <div class="card" style="min-width:260px;flex:0 0 260px">
        @if($threads->isEmpty())
            <div class="empty">{{ __('ui.no_messages') }}</div>
        @else
            @foreach($threads as $thread)
                <a href="{{ route('chat.show', $thread['user']) }}"
                   style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;padding:.6rem .25rem;border-bottom:1px solid rgba(0,0,0,.06);text-decoration:none;{{ $thread['user']->id === $user->id ? 'font-weight:600' : '' }}">
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
                    @if($thread['unread'] > 0)
                        <span class="badge">{{ $thread['unread'] }}</span>
                    @endif
                </a>
            @endforeach
        @endif
    </div>

    {{-- Conversation --}}
    <div class="card" style="flex:1;min-width:280px">
        <div class="page-head"><h1>{{ $user->name }}</h1></div>

        <div style="display:flex;flex-direction:column;gap:.6rem;margin-bottom:1rem">
            @if($messages->isEmpty())
                <div class="empty">{{ __('ui.no_messages') }}</div>
            @else
                @foreach($messages as $m)
                    @php $mine = $m->sender_id === auth()->id(); @endphp
                    <div style="text-align:{{ $mine ? 'right' : 'left' }}">
                        <div style="display:inline-block;max-width:75%;text-align:left;padding:.5rem .75rem;border-radius:.5rem;background:{{ $mine ? 'rgba(59,130,246,.12)' : 'rgba(0,0,0,.05)' }}">
                            @if($mine)
                                <span class="badge">{{ __('ui.you') }}</span>
                            @endif
                            @if($m->replyTo)
                                <div class="text-muted" style="border-left:3px solid rgba(0,0,0,.15);padding-left:.5rem;margin:.25rem 0;font-size:.85em;font-style:italic">
                                    {{ \Illuminate\Support\Str::limit($m->replyTo->body, 60) }}
                                </div>
                            @endif
                            @if($m->message_type === 'file')
                                <div><a href="{{ asset('storage/'.$m->attachment_path) }}"><i class="fa-solid fa-paperclip" aria-hidden="true"></i> {{ $m->attachment_name }}</a></div>
                            @endif
                            @if(!empty($m->body))
                                <div>{{ $m->body }}</div>
                            @endif
                            <div class="text-muted" style="font-size:.72em;margin-top:.2rem">{{ $m->created_at->format('d M H:i') }}</div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Composer --}}
        <form method="POST" action="{{ route('chat.send', $user) }}" enctype="multipart/form-data">
            @csrf
            <div>
                <textarea name="body" rows="2" placeholder="{{ __('ui.type_a_message') }}">{{ old('body') }}</textarea>
                @error('body')<div class="text-danger" style="font-size:.85em">{{ $message }}</div>@enderror
                @error('recipient')<div class="text-danger" style="font-size:.85em">{{ $message }}</div>@enderror
            </div>
            <div style="display:flex;gap:.75rem;align-items:center;margin-top:.5rem;flex-wrap:wrap">
                <input type="file" name="attachment">
                <button class="btn" type="submit">{{ __('ui.send') }}</button>
            </div>
            @error('attachment')<div class="text-danger" style="font-size:.85em">{{ $message }}</div>@enderror
        </form>
    </div>

</div>
@endsection
