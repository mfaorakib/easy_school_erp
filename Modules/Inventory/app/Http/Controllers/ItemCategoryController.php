<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Models\ItemCategory;

class ItemCategoryController extends Controller
{
    public function index()
    {
        return view('inventory::categories.index', ['categories' => ItemCategory::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('inventory::categories.form', ['category' => new ItemCategory]);
    }

    public function store(Request $request)
    {
        ItemCategory::create($request->validate(['name' => ['required', 'string', 'max:150']]));

        return redirect()->route('inventory.categories.index')->with('status', 'Category created.');
    }

    public function edit(ItemCategory $category)
    {
        return view('inventory::categories.form', compact('category'));
    }

    public function update(Request $request, ItemCategory $category)
    {
        $category->update($request->validate(['name' => ['required', 'string', 'max:150']]));

        return redirect()->route('inventory.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(ItemCategory $category)
    {
        $category->delete();

        return redirect()->route('inventory.categories.index')->with('status', 'Category deleted.');
    }
}
