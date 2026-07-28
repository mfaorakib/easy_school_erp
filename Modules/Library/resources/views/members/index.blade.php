@extends('layouts.admin')
@section('title', __('ui.library_members'))

@section('content')
<div class="page-head"><h1>{{ __('ui.library_members') }}</h1></div>

<div class="card">
    <h3 style="margin-top:0">{{ __('ui.add') }}</h3>
    <form method="POST" action="{{ route('library.members.store') }}">
        @csrf
        <div class="grid">
            <div style="grid-column:1/-1"><label>User</label>
                <select name="user_id" required>
                    <option value="">—</option>
                    @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email ?? $u->username }})</option>@endforeach
                </select>
            </div>
            <div style="display:flex;align-items:flex-end"><button class="btn">{{ __('ui.add') }}</button></div>
        </div>
    </form>
</div>

<div class="card">
    @if($members->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Member #</th><th>{{ __('ui.name') }}</th><th>Type</th><th></th></tr></thead>
        <tbody>
        @foreach($members as $m)
            <tr>
                <td>{{ $m->member_no }}</td>
                <td><strong>{{ optional($m->user)->name }}</strong></td>
                <td><span class="badge">{{ ucfirst($m->type) }}</span></td>
                <td class="actions">
                    <form method="POST" action="{{ route('library.members.destroy', $m) }}" onsubmit="return confirm('Remove?')">
                        @csrf @method('DELETE')<button class="btn btn-sm btn-danger">{{ __('ui.delete') }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
