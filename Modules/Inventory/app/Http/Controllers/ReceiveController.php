<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\ItemReceive;
use Modules\Inventory\Models\ItemStore;
use Modules\Inventory\Models\Supplier;
use Modules\Inventory\Services\InventoryService;
use RuntimeException;

class ReceiveController extends Controller
{
    public function index()
    {
        return view('inventory::receive.index', [
            'items'     => Item::where('is_active', true)->orderBy('name')->get(),
            'stores'    => ItemStore::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'recent'    => ItemReceive::with(['item', 'store', 'supplier'])->latest()->limit(20)->get(),
            'today'     => now()->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_id'       => ['required', 'integer', 'exists:items,id'],
            'item_store_id' => ['nullable', 'integer', 'exists:item_stores,id'],
            'supplier_id'   => ['nullable', 'integer', 'exists:suppliers,id'],
            'quantity'      => ['required', 'integer', 'min:1'],
            'unit_price'    => ['nullable', 'numeric', 'min:0'],
            'received_on'   => ['required', 'date'],
            'note'          => ['nullable', 'string'],
        ]);

        app(InventoryService::class)->receive($data);

        return redirect()->route('inventory.receive.index')->with('status', 'Stock received.');
    }

    public function voucher(ItemReceive $receive)
    {
        $receive->load(['item', 'store', 'supplier']);

        return view('inventory::receive.voucher', compact('receive'));
    }

    public function adjust(Request $request, ItemReceive $receive, InventoryService $inventory)
    {
        $data = $request->validate([
            'return_quantity' => ['required', 'integer', 'min:1'],
            'reason'          => ['required', 'string', 'max:255'],
        ]);

        try {
            $inventory->adjustReceive($receive, (int) $data['return_quantity'], $data['reason']);
        } catch (RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()->route('inventory.receive.index')->with('status', 'Return recorded.');
    }
}
