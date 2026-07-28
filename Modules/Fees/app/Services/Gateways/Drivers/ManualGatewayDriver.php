<?php

namespace Modules\Fees\Services\Gateways\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Modules\Fees\Models\FeePaymentIntent;
use Modules\Fees\Services\Gateways\Contracts\PaymentGatewayDriver;
use Modules\Settings\Models\PaymentMethod;

/**
 * Cash, bank transfer, or any mobile-banking channel with no live API wired
 * up (e.g. Rocket) — no external checkout. The guardian is sent to our own
 * "enter your transaction reference" page; a staff member then confirms the
 * intent by hand once the money is verified to have arrived (see
 * FeePaymentIntentService::complete(), called directly from the admin side —
 * this driver's verify() is never the completion path for manual payments).
 */
class ManualGatewayDriver implements PaymentGatewayDriver
{
    public function checkout(FeePaymentIntent $intent, PaymentMethod $method): string
    {
        return URL::route('portal.pay.manual', $intent->token);
    }

    public function verify(FeePaymentIntent $intent, PaymentMethod $method, Request $request): array
    {
        return [
            'success'           => false,
            'gateway_reference' => null,
            'message'           => 'This payment method requires manual confirmation by school staff.',
            'raw'               => [],
        ];
    }
}
