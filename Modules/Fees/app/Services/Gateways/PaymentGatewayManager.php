<?php

namespace Modules\Fees\Services\Gateways;

use Modules\Fees\Services\Gateways\Contracts\PaymentGatewayDriver;
use Modules\Fees\Services\Gateways\Drivers\BkashGatewayDriver;
use Modules\Fees\Services\Gateways\Drivers\ManualGatewayDriver;
use Modules\Fees\Services\Gateways\Drivers\NagadGatewayDriver;
use Modules\Fees\Services\Gateways\Drivers\StripeGatewayDriver;
use Modules\Settings\Models\PaymentMethod;

/** Resolves the right PaymentGatewayDriver for a PaymentMethod's `driver` column. */
class PaymentGatewayManager
{
    public function __construct(
        private readonly ManualGatewayDriver $manual,
        private readonly StripeGatewayDriver $stripe,
        private readonly BkashGatewayDriver $bkash,
        private readonly NagadGatewayDriver $nagad,
    ) {}

    public function driver(PaymentMethod|string $method): PaymentGatewayDriver
    {
        $key = $method instanceof PaymentMethod ? $method->driver : $method;

        return match ($key) {
            PaymentMethod::DRIVER_STRIPE => $this->stripe,
            PaymentMethod::DRIVER_BKASH  => $this->bkash,
            PaymentMethod::DRIVER_NAGAD  => $this->nagad,
            default                      => $this->manual,
        };
    }
}
