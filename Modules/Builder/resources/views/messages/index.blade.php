@extends('layouts.admin')
@section('title', 'Messages')

@section('content')
<div class="page-head"><h1>{{ __('ui.messages_inbox') }}</h1></div>

<div class="card">
    @if($messages->isEmpty())
        <div class="empty">{{ __('ui.no_messages') }}</div>
    @else
    <table>
        <thead><tr>
            <th>{{ __('ui.type') }}</th><th>{{ __('ui.name') }}</th><th>{{ __('ui.email') }}</th>
            <th>{{ __('ui.subject') }}</th><th>{{ __('ui.date') }}</th><th>{{ __('ui.actions') }}</th>
        </tr></thead>
        <tbody>
        @foreach($messages as $m)
            <tr>
                <td><span class="badge">{{ $m->type }}</span></td>
                <td>{{ $m->name ?: '—' }}</td>
                <td>{{ $m->email }}</td>
                <td>
                    <strong>{{ $m->subject ?: '—' }}</strong>
                    @if($m->body)<div style="color:#64748b;font-size:.9rem">{{ \Illuminate\Support\Str::limit($m->body, 90) }}</div>@endif
                </td>
                <td>{{ $m->created_at->format('d M Y') }}</td>
                <td class="actions">
                    <form method="POST" action="{{ route('builder.messages.destroy', $m) }}" onsubmit="return confirm('{{ __('ui.delete') }}?')">
                        @csrf @method('DELETE')<button class="btn btn-sm btn-danger">{{ __('ui.delete') }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>

<div style="margin-top:1rem">{{ $messages->links() }}</div>
@endsection
