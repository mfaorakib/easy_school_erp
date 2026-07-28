<?php

namespace Modules\Fees\Services\Gateways\Contracts;

use Illuminate\Http\Request;
use Modules\Fees\Models\FeePaymentIntent;
use Modules\Settings\Models\PaymentMethod;

/**
 * Every payment channel — real gateway or manual — implements this so the
 * checkout flow stays gateway-agnostic. checkout() is called once, when the
 * guardian clicks "Pay Now"; verify() resolves the outcome when the gateway
 * sends the guardian back (return URL) or a webhook lands.
 */
interface PaymentGatewayDriver
{
    /** Start the payment. Returns the URL to redirect the guardian's browser to. */
    public function checkout(FeePaymentIntent $intent, PaymentMethod $method): string;

    /**
     * Resolve the outcome of a return/webhook request. Read-only — does not
     * mutate the intent; FeePaymentIntentService decides what to do with the
     * result so completion logic lives in exactly one place.
     *
     * @return array{success:bool, gateway_reference:?string, message:?string, raw:array}
     */
    public function verify(FeePaymentIntent $intent, PaymentMethod $method, Request $request): array;
}
