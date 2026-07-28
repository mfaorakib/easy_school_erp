<?php

namespace Modules\Fees\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Fees\Models\FeeType;

class FeeTypeController extends Controller
{
    public function index()
    {
        return view('fees::types.index', ['types' => FeeType::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('fees::types.form', ['type' => new FeeType]);
    }

    public function store(Request $request)
    {
        FeeType::create($this->validated($request));

        return redirect()->route('fees.types.index')->with('status', 'Fee type created.');
    }

    public function edit(FeeType $type)
    {
        return view('fees::types.form', compact('type'));
    }

    public function update(Request $request, FeeType $type)
    {
        $type->update($this->validated($request));

        return redirect()->route('fees.types.index')->with('status', 'Fee type updated.');
    }

    public function destroy(FeeType $type)
    {
        $type->delete();

        return redirect()->route('fees.types.index')->with('status', 'Fee type deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'code'        => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
