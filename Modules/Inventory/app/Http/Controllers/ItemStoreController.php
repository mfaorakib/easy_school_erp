<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Models\ItemStore;

class ItemStoreController extends Controller
{
    public function index()
    {
        return view('inventory::stores.index', ['stores' => ItemStore::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('inventory::stores.form', ['store' => new ItemStore]);
    }

    public function store(Request $request)
    {
        ItemStore::create($this->validated($request));

        return redirect()->route('inventory.stores.index')->with('status', 'Store created.');
    }

    public function edit(ItemStore $store)
    {
        return view('inventory::stores.form', compact('store'));
    }

    public function update(Request $request, ItemStore $store)
    {
        $store->update($this->validated($request));

        return redirect()->route('inventory.stores.index')->with('status', 'Store updated.');
    }

    public function destroy(ItemStore $store)
    {
        $store->delete();

        return redirect()->route('inventory.stores.index')->with('status', 'Store deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50'],
        ]);
    }
}
