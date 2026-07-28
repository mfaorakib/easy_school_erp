<?php

namespace Modules\Fees\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Fees\Models\FeePaymentIntent;
use Modules\Fees\Services\FeePaymentIntentService;

class FeePaymentIntentController extends Controller
{
    public function __construct(private readonly FeePaymentIntentService $service) {}

    public function index()
    {
        $intents = FeePaymentIntent::with(['assignment.student', 'assignment.master.type', 'method', 'initiator'])
            ->where('status', FeePaymentIntent::STATUS_PENDING)
            ->latest()
            ->paginate(20);

        return view('fees::intents.index', compact('intents'));
    }

    public function confirm(FeePaymentIntent $intent)
    {
        abort_unless($intent->isPending(), 422, 'Already processed.');

        $this->service->complete($intent, $intent->gateway_reference);

        return redirect()->route('fees.intents.index')->with('status', 'Payment confirmed.');
    }

    public function reject(Request $request, FeePaymentIntent $intent)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        abort_unless($intent->isPending(), 422, 'Already processed.');

        $this->service->fail($intent, $data['reason']);

        return redirect()->route('fees.intents.index')->with('status', 'Payment rejected.');
    }
}
