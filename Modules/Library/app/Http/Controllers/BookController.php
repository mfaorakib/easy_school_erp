<?php

namespace Modules\Library\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Library\Models\Book;
use Modules\Library\Models\BookCategory;

class BookController extends Controller
{
    public function index()
    {
        return view('library::books.index', ['books' => Book::with('category')->orderBy('title')->get()]);
    }

    public function create()
    {
        return view('library::books.form', ['book' => new Book, 'categories' => BookCategory::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['available'] = $data['quantity']; // new stock is fully available
        Book::create($data);

        return redirect()->route('library.books.index')->with('status', 'Book added.');
    }

    public function edit(Book $book)
    {
        return view('library::books.form', ['book' => $book, 'categories' => BookCategory::orderBy('name')->get()]);
    }

    public function update(Request $request, Book $book)
    {
        $data = $this->validated($request);
        // Keep available in step with quantity changes without going negative.
        $issued = $book->quantity - $book->available;
        $data['available'] = max(0, $data['quantity'] - $issued);
        $book->update($data);

        return redirect()->route('library.books.index')->with('status', 'Book updated.');
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return redirect()->route('library.books.index')->with('status', 'Book deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'            => ['required', 'string', 'max:200'],
            'author'           => ['nullable', 'string', 'max:150'],
            'publisher'        => ['nullable', 'string', 'max:150'],
            'isbn'             => ['nullable', 'string', 'max:50'],
            'book_category_id' => ['nullable', 'integer', 'exists:book_categories,id'],
            'rack'             => ['nullable', 'string', 'max:50'],
            'quantity'         => ['required', 'integer', 'min:1'],
            'price'            => ['nullable', 'numeric', 'min:0'],
        ]);
    }
}
