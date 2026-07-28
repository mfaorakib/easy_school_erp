@extends('layouts.admin')
@section('title', __('ui.book_categories'))

@section('content')
<div class="page-head"><h1>{{ __('ui.book_categories') }}</h1>
    <a href="{{ route('library.categories.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>
<div class="card">
    @if($categories->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($categories as $c)
            <tr>
                <td><strong>{{ $c->name }}</strong></td>
                <td class="actions">
                    <a href="{{ route('library.categories.edit', $c) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('library.categories.destroy', $c) }}" onsubmit="return confirm('Delete?')">
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
