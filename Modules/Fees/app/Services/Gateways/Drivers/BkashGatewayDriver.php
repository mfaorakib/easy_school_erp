<?php

namespace Modules\Fees\Services\Gateways\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Modules\Fees\Models\FeePaymentIntent;
use Modules\Fees\Services\Gateways\Contracts\PaymentGatewayDriver;
use Modules\Settings\Models\PaymentMethod;

/**
 * bKash Tokenized Checkout (grant token → create payment → execute payment),
 * built against bKash's publicly documented sandbox contract. Config keys
 * (PaymentMethod.config): app_key, app_secret, username, password, sandbox
 * (bool). Endpoint paths/field names should be re-verified against bKash's
 * current PDF integration guide before going live — third-party API paths
 * can drift between versions and this was not run against a live sandbox
 * from this environment.
 */
class BkashGatewayDriver implements PaymentGatewayDriver
{
    public function checkout(FeePaymentIntent $intent, PaymentMethod $method): string
    {
        $token = $this->grantToken($method);

        $response = Http::withHeaders($this->authHeaders($method, $token))
            ->post($this->baseUrl($method).'/tokenized/checkout/create', [
                'amount'                => (string) $intent->amount,
                'currency'              => 'BDT',
                'intent'                => 'sale',
                'merchantInvoiceNumber' => $intent->token,
                'callbackURL'           => URL::route('portal.pay.return', ['token' => $intent->token]),
            ])
            ->throw()->json();

        $intent->update([
            'gateway_reference' => $response['paymentID'] ?? null,
            'gateway_payload'   => json_encode($response),
        ]);

        return $response['bkashURL']
            ?? URL::route('portal.pay.return', ['token' => $intent->token, 'error' => 'bkash_checkout_url_missing']);
    }

    public function verify(FeePaymentIntent $intent, PaymentMethod $method, Request $request): array
    {
        $status = $request->query('status');
        if (in_array($status, ['cancel', 'failure'], true)) {
            return ['success' => false, 'gateway_reference' => $intent->gateway_reference, 'message' => "Payment {$status} at bKash.", 'raw' => []];
        }

        $paymentId = $request->query('paymentID', $intent->gateway_reference);
        if (! $paymentId) {
            return ['success' => false, 'gateway_reference' => null, 'message' => 'Missing paymentID.', 'raw' => []];
        }

        $token = $this->grantToken($method);
        $response = Http::withHeaders($this->authHeaders($method, $token))
            ->post($this->baseUrl($method)."/tokenized/checkout/execute/{$paymentId}")
            ->throw()->json();

        $success = ($response['transactionStatus'] ?? null) === 'Completed';

        return [
            'success'           => $success,
            'gateway_reference' => $response['trxID'] ?? $paymentId,
            'message'           => $success ? null : ($response['statusMessage'] ?? 'Payment not completed.'),
            'raw'               => $response,
        ];
    }

    private function grantToken(PaymentMethod $method): string
    {
        $response = Http::withHeaders([
            'username'     => $method->configValue('username'),
            'password'     => $method->configValue('password'),
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ])->post($this->baseUrl($method).'/tokenized/checkout/token/grant', [
            'app_key'    => $method->configValue('app_key'),
            'app_secret' => $method->configValue('app_secret'),
        ])->throw()->json();

        return $response['id_token'];
    }

    private function authHeaders(PaymentMethod $method, string $token): array
    {
        return [
            'Authorization' => $token,
            'X-APP-Key'     => $method->configValue('app_key'),
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

    private function baseUrl(PaymentMethod $method): string
    {
        if ($override = $method->configValue('base_url')) {
            return rtrim($override, '/');
        }

        return $method->configValue('sandbox', true)
            ? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta'
            : 'https://tokenized.pay.bka.sh/v1.2.0-beta';
    }
}
