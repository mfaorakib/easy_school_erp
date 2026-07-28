<?php

namespace Modules\Fees\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Fees\Models\FeePayment;
use Modules\Fees\Services\FeeService;
use RuntimeException;

class FeeRefundController extends Controller
{
    public function store(Request $request, FeePayment $payment, FeeService $fees)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        try {
            $fees->refund($payment, (float) $data['amount'], $data['reason']);
        } catch (RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()->route('fees.payments.receipt', $payment)->with('status', 'Refund recorded.');
    }
}
