<?php

namespace Modules\Fees\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Fees\Models\FeeAssignment;
use Modules\Fees\Models\FeeDiscount;
use Modules\Fees\Services\FeeService;

class FeeAdjustmentController extends Controller
{
    public function attach(Request $request, FeeAssignment $assignment, FeeService $service)
    {
        $data = $request->validate([
            'fee_discount_id' => ['required', 'integer', 'exists:fee_discounts,id'],
            'reason'          => ['required', 'string', 'max:500'],
        ]);
        $discount = FeeDiscount::findOrFail($data['fee_discount_id']);
        $service->attachDiscount($assignment, $discount, $data['reason'], $request->user());

        return redirect()->route('fees.collect.index', ['student_id' => $assignment->student_id])->with('status', 'Discount applied.');
    }

    public function detach(FeeAssignment $assignment, FeeDiscount $discount, FeeService $service)
    {
        $service->detachDiscount($assignment, $discount);

        return redirect()->route('fees.collect.index', ['student_id' => $assignment->student_id])->with('status', 'Discount removed.');
    }
}
