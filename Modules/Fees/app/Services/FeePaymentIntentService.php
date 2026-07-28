<?php

namespace Modules\Fees\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Fees\Models\FeeAssignment;
use Modules\Fees\Models\FeePayment;
use Modules\Fees\Models\FeePaymentIntent;
use Modules\Fees\Services\Gateways\PaymentGatewayManager;
use Modules\Settings\Models\PaymentMethod;

/**
 * Orchestrates one online-checkout attempt end to end. Gateway drivers stay
 * read-only (see PaymentGatewayDriver::verify() docblock) — every state
 * change (pending → completed/failed, and the resulting FeePayment) happens
 * here, in one place, regardless of which driver or channel triggered it.
 */
class FeePaymentIntentService
{
    public function __construct(
        private readonly PaymentGatewayManager $gateways,
        private readonly FeeService $fees,
    ) {}

    /** Start a checkout: creates a pending intent and returns where to send the guardian's browser. */
    public function start(FeeAssignment $assignment, PaymentMethod $method, float $amount, ?User $initiator): array
    {
        $intent = FeePaymentIntent::create([
            'token'             => $this->uniqueToken(),
            'fee_assignment_id' => $assignment->id,
            'payment_method_id' => $method->id,
            'amount'            => $amount,
            'initiated_by'      => $initiator?->id,
            'status'            => FeePaymentIntent::STATUS_PENDING,
        ]);

        $redirectUrl = $this->gateways->driver($method)->checkout($intent, $method);

        return ['intent' => $intent, 'redirect_url' => $redirectUrl];
    }

    /**
     * Resolve a gateway return/webhook request for a given intent token: asks
     * the driver to verify, then completes or fails the intent accordingly.
     */
    public function resolveReturn(string $token, Request $request): array
    {
        $intent = FeePaymentIntent::where('token', $token)->firstOrFail();

        if (! $intent->isPending()) {
            return ['intent' => $intent, 'success' => $intent->status === FeePaymentIntent::STATUS_COMPLETED, 'message' => null];
        }

        $method = $intent->method;
        $result = $this->gateways->driver($method)->verify($intent, $method, $request);

        if ($result['success']) {
            $this->complete($intent, $result['gateway_reference'], $result['raw'] ?? []);
        } else {
            $this->fail($intent, $result['message'] ?? 'Payment not completed.');
        }

        return ['intent' => $intent->fresh(), 'success' => (bool) $result['success'], 'message' => $result['message'] ?? null];
    }

    /** Mark an intent completed and record the real FeePayment through the same path manual collection uses. */
    public function complete(FeePaymentIntent $intent, ?string $gatewayReference, array $raw = []): FeePayment
    {
        return DB::transaction(function () use ($intent, $gatewayReference, $raw) {
            $payment = $this->fees->collect($intent->assignment, [
                'amount'      => $intent->amount,
                'method'      => $intent->method->name,
                'reference'   => $gatewayReference,
                'note'        => 'Online payment via '.$intent->method->name,
                'received_by' => $intent->initiated_by,
            ]);

            $intent->update([
                'status'            => FeePaymentIntent::STATUS_COMPLETED,
                'gateway_reference' => $gatewayReference ?? $intent->gateway_reference,
                'gateway_payload'   => $raw ? json_encode($raw) : $intent->gateway_payload,
                'fee_payment_id'    => $payment->id,
            ]);

            return $payment;
        });
    }

    public function fail(FeePaymentIntent $intent, string $reason): void
    {
        $intent->update(['status' => FeePaymentIntent::STATUS_FAILED, 'failure_reason' => $reason]);
    }

    private function uniqueToken(): string
    {
        do {
            $token = Str::upper(Str::random(16));
        } while (FeePaymentIntent::where('token', $token)->exists());

        return $token;
    }
}
