<?php

namespace Modules\Fees\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Fees\Models\FeePayment;

class FeePaymentReceiptController extends Controller
{
    public function show(FeePayment $payment)
    {
        $payment->load(['assignment.student', 'assignment.master.type', 'receiver']);

        return view('fees::payments.receipt', compact('payment'));
    }
}
