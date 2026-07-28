<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\ItemCategory;

class ItemController extends Controller
{
    public function index()
    {
        return view('inventory::items.index', ['items' => Item::with('category')->orderBy('name')->get()]);
    }

    public function create()
    {
        return view('inventory::items.form', ['item' => new Item, 'categories' => ItemCategory::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        Item::create($this->validated($request));

        return redirect()->route('inventory.items.index')->with('status', 'Item added.');
    }

    public function edit(Item $item)
    {
        return view('inventory::items.form', ['item' => $item, 'categories' => ItemCategory::orderBy('name')->get()]);
    }

    public function update(Request $request, Item $item)
    {
        $item->update($this->validated($request));

        return redirect()->route('inventory.items.index')->with('status', 'Item updated.');
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return redirect()->route('inventory.items.index')->with('status', 'Item deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'             => ['required', 'string', 'max:200'],
            'item_category_id' => ['nullable', 'exists:item_categories,id'],
            'unit'             => ['nullable', 'string', 'max:30'],
            'description'      => ['nullable', 'string', 'max:255'],
        ]);
    }
}
