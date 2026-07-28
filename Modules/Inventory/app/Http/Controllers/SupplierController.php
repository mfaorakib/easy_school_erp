<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Models\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        return view('inventory::suppliers.index', ['suppliers' => Supplier::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('inventory::suppliers.form', ['supplier' => new Supplier]);
    }

    public function store(Request $request)
    {
        Supplier::create($this->validated($request));

        return redirect()->route('inventory.suppliers.index')->with('status', 'Supplier created.');
    }

    public function edit(Supplier $supplier)
    {
        return view('inventory::suppliers.form', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($this->validated($request));

        return redirect()->route('inventory.suppliers.index')->with('status', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('inventory.suppliers.index')->with('status', 'Supplier deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
