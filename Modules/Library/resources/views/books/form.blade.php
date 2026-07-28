@extends('layouts.admin')
@section('title', __('ui.books'))

@section('content')
<div class="page-head"><h1>{{ $book->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.books') }}</h1>
    <a href="{{ route('library.books.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ $book->exists ? route('library.books.update', $book) : route('library.books.store') }}">
        @csrf @if($book->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>Title *</label><input name="title" value="{{ old('title', $book->title) }}" required></div>
            <div><label>Author</label><input name="author" value="{{ old('author', $book->author) }}"></div>
            <div><label>Publisher</label><input name="publisher" value="{{ old('publisher', $book->publisher) }}"></div>
            <div><label>ISBN</label><input name="isbn" value="{{ old('isbn', $book->isbn) }}"></div>
            <div><label>Category</label>
                <select name="book_category_id"><option value="">—</option>
                    @foreach($categories as $c)<option value="{{ $c->id }}" {{ old('book_category_id', $book->book_category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                </select></div>
            <div><label>Rack</label><input name="rack" value="{{ old('rack', $book->rack) }}"></div>
            <div><label>Quantity *</label><input name="quantity" type="number" min="1" value="{{ old('quantity', $book->quantity ?? 1) }}" required></div>
            <div><label>Price</label><input name="price" type="number" step="any" value="{{ old('price', $book->price) }}"></div>
        </div>
        @if($book->exists)<p class="empty" style="text-align:left;color:var(--muted);padding:.5rem 0 0">Available now: {{ $book->available }} / {{ $book->quantity }}</p>@endif
        <div style="margin-top:1.25rem"><button class="btn">{{ $book->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
