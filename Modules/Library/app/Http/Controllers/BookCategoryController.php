<?php

namespace Modules\Library\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Library\Models\BookCategory;

class BookCategoryController extends Controller
{
    public function index()
    {
        return view('library::categories.index', ['categories' => BookCategory::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('library::categories.form', ['category' => new BookCategory]);
    }

    public function store(Request $request)
    {
        BookCategory::create($request->validate(['name' => ['required', 'string', 'max:150']]));

        return redirect()->route('library.categories.index')->with('status', 'Category created.');
    }

    public function edit(BookCategory $category)
    {
        return view('library::categories.form', compact('category'));
    }

    public function update(Request $request, BookCategory $category)
    {
        $category->update($request->validate(['name' => ['required', 'string', 'max:150']]));

        return redirect()->route('library.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(BookCategory $category)
    {
        $category->delete();

        return redirect()->route('library.categories.index')->with('status', 'Category deleted.');
    }
}
