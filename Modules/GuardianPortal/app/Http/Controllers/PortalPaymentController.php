<?php

namespace Modules\GuardianPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Fees\Models\FeeAssignment;
use Modules\Fees\Models\FeePayment;
use Modules\Fees\Models\FeePaymentIntent;
use Modules\Fees\Services\FeePaymentIntentService;
use Modules\GuardianPortal\Services\GuardianPortalService;
use Modules\Settings\Models\PaymentMethod;

/**
 * Guardian-only payment flow: start a gateway checkout, handle the return,
 * accept a manual reference, and show payment history/receipts. Every action
 * tied to a specific fee assignment or payment must be verified against
 * GuardianPortalService::children() first — see the abort_unless() checks in
 * start() and receipt() — so a guardian can never touch another family's
 * fee data by guessing an id in the URL.
 */
class PortalPaymentController extends Controller
{
    public function __construct(
        private readonly GuardianPortalService $portal,
        private readonly FeePaymentIntentService $intents,
    ) {}

    public function start(Request $request)
    {
        $data = $request->validate([
            'fee_assignment_id' => ['required', 'integer', 'exists:fee_assignments,id'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'amount'             => ['required', 'numeric', 'min:0.01'],
        ]);

        $assignment = FeeAssignment::findOrFail($data['fee_assignment_id']);
        $ownChildIds = $this->portal->children($request->user())->pluck('id');
        abort_unless($ownChildIds->contains($assignment->student_id), 403);

        $method = PaymentMethod::findOrFail($data['payment_method_id']);

        // A misconfigured/placeholder gateway (e.g. test keys not yet replaced with real
        // ones) must never crash the guardian's browser with a raw error page.
        try {
            $result = $this->intents->start($assignment, $method, (float) $data['amount'], $request->user());
        } catch (\Throwable $e) {
            Log::error('Payment checkout failed to start', ['method' => $method->name, 'error' => $e->getMessage()]);

            return redirect()->route('portal.children.show', $assignment->student_id)
                ->with('error', "We couldn't start the payment with {$method->name} right now. Please try another method or contact the school office.");
        }

        return redirect()->away($result['redirect_url']);
    }

    public function return(string $token, Request $request)
    {
        try {
            $result = $this->intents->resolveReturn($token, $request);
        } catch (\Throwable $e) {
            Log::error('Payment verification failed', ['token' => $token, 'error' => $e->getMessage()]);

            return redirect()->route('portal.dashboard')
                ->with('error', "We couldn't confirm this payment automatically — if you were charged, please contact the school office with your reference.");
        }

        if ($result['success']) {
            return redirect()->route('portal.payments.index')->with('status', 'Payment successful! Your receipt is ready below.');
        }

        return redirect()->route('portal.dashboard')->with('error', $result['message'] ?? 'Payment was not completed.');
    }

    public function manualForm(string $token)
    {
        $intent = FeePaymentIntent::with(['assignment.master.type', 'method'])->where('token', $token)->firstOrFail();

        return view('guardianportal::pay-manual', compact('intent'));
    }

    public function manualSubmit(string $token, Request $request)
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:100'],
        ]);

        $intent = FeePaymentIntent::where('token', $token)->firstOrFail();
        abort_unless($intent->isPending(), 422, 'This payment has already been processed.');

        $intent->update(['gateway_reference' => $data['reference']]);

        return redirect()->route('portal.dashboard')
            ->with('status', 'Thanks! We have received your reference and will confirm your payment shortly.');
    }

    public function history(Request $request)
    {
        $payments = $this->portal->paymentHistory($request->user());

        return view('guardianportal::payments', compact('payments'));
    }

    public function receipt(FeePayment $payment, Request $request)
    {
        $ownChildIds = $this->portal->children($request->user())->pluck('id');
        abort_unless($ownChildIds->contains($payment->assignment->student_id), 403);

        return view('guardianportal::receipt', compact('payment'));
    }
}
