@extends('layouts.admin')
@section('title', 'Notice Board')

@section('content')
<div class="page-head"><h1>Notice Board</h1>
    <a href="{{ route('communication.notices.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($notices->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Title</th><th>Date</th><th>Audience</th><th>Published</th><th></th></tr></thead>
        <tbody>
        @foreach($notices as $notice)
            <tr>
                <td><strong>{{ $notice->title }}</strong></td>
                <td>{{ optional($notice->notice_date)->format('d M Y') }}</td>
                <td>
                    @foreach($notice->audiences ?? [] as $audience)
                        <span class="badge">{{ $audience }}</span>
                    @endforeach
                </td>
                <td><span class="badge">{{ $notice->is_published ? 'Yes' : 'No' }}</span></td>
                <td class="actions">
                    <a href="{{ route('communication.notices.edit', $notice) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('communication.notices.destroy', $notice) }}" onsubmit="return confirm('Delete?')">
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
