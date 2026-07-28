<?php

namespace Modules\Fees\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Fees\Models\FeeDiscount;

class FeeDiscountController extends Controller
{
    public function index()
    {
        return view('fees::discounts.index', ['discounts' => FeeDiscount::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('fees::discounts.form', ['discount' => new FeeDiscount]);
    }

    public function store(Request $request)
    {
        FeeDiscount::create($this->validated($request));

        return redirect()->route('fees.discounts.index')->with('status', 'Discount created.');
    }

    public function edit(FeeDiscount $discount)
    {
        return view('fees::discounts.form', compact('discount'));
    }

    public function update(Request $request, FeeDiscount $discount)
    {
        $discount->update($this->validated($request));

        return redirect()->route('fees.discounts.index')->with('status', 'Discount updated.');
    }

    public function destroy(FeeDiscount $discount)
    {
        $discount->delete();

        return redirect()->route('fees.discounts.index')->with('status', 'Discount deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'code'        => ['nullable', 'string', 'max:50'],
            'amount_type' => ['required', 'in:percentage,fixed'],
            'amount'      => ['required', 'numeric', 'min:0'],
        ]);
    }
}
