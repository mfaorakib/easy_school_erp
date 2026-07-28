@extends('layouts.admin')
@section('title', __('ui.books'))

@section('content')
<div class="page-head"><h1>{{ __('ui.books') }}</h1>
    <a href="{{ route('library.books.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($books->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Title</th><th>Author</th><th>Category</th><th>Rack</th><th>Qty</th><th>{{ __('ui.available') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($books as $b)
            <tr>
                <td><strong>{{ $b->title }}</strong></td>
                <td>{{ $b->author ?? '—' }}</td>
                <td>{{ optional($b->category)->name ?? '—' }}</td>
                <td>{{ $b->rack ?? '—' }}</td>
                <td>{{ $b->quantity }}</td>
                <td><span class="badge">{{ $b->available }}</span></td>
                <td class="actions">
                    <a href="{{ route('library.books.edit', $b) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('library.books.destroy', $b) }}" onsubmit="return confirm('Delete?')">
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
