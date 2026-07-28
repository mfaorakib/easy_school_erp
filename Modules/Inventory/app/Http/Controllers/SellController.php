<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\ItemSell;
use Modules\Inventory\Models\ItemStore;
use Modules\Inventory\Services\InventoryService;
use RuntimeException;

class SellController extends Controller
{
    public function index()
    {
        return view('inventory::sell.index', [
            'items'  => Item::where('is_active', true)->orderBy('name')->get(),
            'stores' => ItemStore::orderBy('name')->get(),
            'users'  => User::where('is_active', true)->orderBy('name')->get(),
            'recent' => ItemSell::with(['item', 'soldTo'])->latest()->limit(20)->get(),
            'today'  => now()->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_id'       => ['required', 'integer', 'exists:items,id'],
            'item_store_id' => ['nullable', 'integer', 'exists:item_stores,id'],
            'sold_to'       => ['nullable', 'integer', 'exists:users,id'],
            'buyer_name'    => ['nullable', 'string', 'max:150'],
            'buyer_contact' => ['nullable', 'string', 'max:40'],
            'quantity'      => ['required', 'integer', 'min:1'],
            'unit_price'    => ['nullable', 'numeric', 'min:0'],
            'sold_on'       => ['required', 'date'],
            'note'          => ['nullable', 'string'],
        ]);

        try {
            app(InventoryService::class)->sell($data);
        } catch (RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()->route('inventory.sell.index')->with('status', 'Item sold.');
    }

    public function voucher(ItemSell $sell)
    {
        $sell->load(['item', 'soldTo']);

        return view('inventory::sell.voucher', compact('sell'));
    }

    public function adjust(Request $request, ItemSell $sell, InventoryService $inventory)
    {
        $data = $request->validate([
            'return_quantity' => ['required', 'integer', 'min:1'],
            'reason'          => ['required', 'string', 'max:255'],
        ]);

        try {
            $inventory->adjustSell($sell, (int) $data['return_quantity'], $data['reason']);
        } catch (RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()->route('inventory.sell.index')->with('status', 'Return recorded.');
    }
}
