<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\ItemIssue;
use Modules\Inventory\Models\ItemStore;
use Modules\Inventory\Services\InventoryService;
use RuntimeException;

class IssueController extends Controller
{
    public function index()
    {
        return view('inventory::issue.index', [
            'items'  => Item::where('is_active', true)->orderBy('name')->get(),
            'stores' => ItemStore::orderBy('name')->get(),
            'users'  => User::where('is_active', true)->orderBy('name')->get(),
            'open'   => ItemIssue::with(['item', 'issuedTo'])
                ->where('is_returned', false)->latest()->get(),
            'today'  => now()->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_id'       => ['required', 'integer', 'exists:items,id'],
            'item_store_id' => ['nullable', 'integer', 'exists:item_stores,id'],
            'issued_to'     => ['nullable', 'integer', 'exists:users,id'],
            'quantity'      => ['required', 'integer', 'min:1'],
            'issue_date'    => ['required', 'date'],
            'note'          => ['nullable', 'string'],
        ]);

        try {
            app(InventoryService::class)->issue($data);
        } catch (RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()->route('inventory.issue.index')->with('status', 'Item issued.');
    }

    public function returnItem(ItemIssue $issue)
    {
        app(InventoryService::class)->returnIssue($issue);

        return redirect()->route('inventory.issue.index')->with('status', 'Item returned.');
    }
}
