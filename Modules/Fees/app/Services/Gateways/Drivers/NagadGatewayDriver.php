<?php

namespace Modules\Fees\Services\Gateways\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Modules\Fees\Models\FeePaymentIntent;
use Modules\Fees\Services\Gateways\Contracts\PaymentGatewayDriver;
use Modules\Settings\Models\PaymentMethod;

/**
 * Nagad Payment Gateway — the documented "Checkout Init → Checkout Complete
 * → Verify" flow with mutual RSA trust: the merchant encrypts request
 * sensitive data with Nagad's PG public key (only Nagad can decrypt) and
 * signs it with the merchant's own private key (Nagad verifies with the
 * merchant's public key, uploaded during onboarding); Nagad's responses are
 * the mirror — encrypted with the merchant's public key, decryptable only
 * with the merchant's private key.
 *
 * Config keys (PaymentMethod.config): merchant_id, merchant_private_key (PEM),
 * pg_public_key (Nagad's PEM, issued at onboarding), sandbox (bool).
 * Endpoint paths should be re-verified against Nagad's current Merchant API
 * Integration Guide before going live — this was built from their publicly
 * documented contract, not run against a live sandbox from this environment.
 */
class NagadGatewayDriver implements PaymentGatewayDriver
{
    public function checkout(FeePaymentIntent $intent, PaymentMethod $method): string
    {
        $merchantId = $method->configValue('merchant_id');
        $orderId    = $intent->token;
        $dateTime   = now()->format('YmdHis');

        $initSensitive = $this->encrypt(['merchantId' => $merchantId, 'datetime' => $dateTime, 'orderId' => $orderId], $method);
        $initSignature = $this->sign(['merchantId' => $merchantId, 'datetime' => $dateTime, 'orderId' => $orderId], $method);

        $initResponse = Http::withHeaders($this->headers())
            ->post($this->baseUrl($method)."/api/dfs/check-out/initialize/{$merchantId}/{$orderId}", [
                'dateTime'     => $dateTime,
                'sensitiveData' => $initSensitive,
                'signature'    => $initSignature,
            ])
            ->throw()->json();

        $initData = $this->decrypt($initResponse['sensitiveData'] ?? '', $method);
        $paymentReferenceId = $initData['paymentReferenceId'] ?? null;
        $challenge = $initData['challenge'] ?? null;

        if (! $paymentReferenceId || ! $challenge) {
            return URL::route('portal.pay.return', ['token' => $intent->token, 'error' => 'nagad_init_failed']);
        }

        $completePayload = [
            'merchantId'   => $merchantId,
            'orderId'      => $orderId,
            'currencyCode' => '050', // ISO 4217 numeric — BDT
            'amount'       => (string) $intent->amount,
            'challenge'    => $challenge,
        ];

        $completeResponse = Http::withHeaders($this->headers())
            ->post($this->baseUrl($method)."/api/dfs/check-out/complete/{$paymentReferenceId}", [
                'sensitiveData'        => $this->encrypt($completePayload, $method),
                'signature'            => $this->sign($completePayload, $method),
                'merchantCallbackURL'  => URL::route('portal.pay.return', ['token' => $intent->token]),
                'additionalMerchantInfo' => [],
            ])
            ->throw()->json();

        $intent->update([
            'gateway_reference' => $paymentReferenceId,
            'gateway_payload'   => json_encode(['init' => $initResponse, 'complete' => $completeResponse]),
        ]);

        return $completeResponse['callBackUrl']
            ?? URL::route('portal.pay.return', ['token' => $intent->token, 'error' => 'nagad_checkout_url_missing']);
    }

    public function verify(FeePaymentIntent $intent, PaymentMethod $method, Request $request): array
    {
        $paymentRefId = $request->query('payment_ref_id', $intent->gateway_reference);
        $returnStatus = $request->query('status');

        if (! $paymentRefId) {
            return ['success' => false, 'gateway_reference' => null, 'message' => 'Missing payment_ref_id.', 'raw' => []];
        }

        if ($returnStatus && ! in_array($returnStatus, ['Success', 'success'], true)) {
            return ['success' => false, 'gateway_reference' => $paymentRefId, 'message' => "Payment {$returnStatus} at Nagad.", 'raw' => []];
        }

        $response = Http::acceptJson()
            ->get($this->baseUrl($method)."/api/dfs/verify/payment/{$paymentRefId}")
            ->throw()->json();

        $success = ($response['status'] ?? null) === 'Success';

        return [
            'success'           => $success,
            'gateway_reference' => $response['issuerPaymentRefId'] ?? $paymentRefId,
            'message'           => $success ? null : ($response['message'] ?? 'Payment not completed.'),
            'raw'               => $response,
        ];
    }

    /** RSA-encrypt with Nagad's PG public key — only Nagad can decrypt. */
    private function encrypt(array $data, PaymentMethod $method): string
    {
        openssl_public_encrypt(json_encode($data), $encrypted, (string) $method->configValue('pg_public_key'));

        return base64_encode($encrypted);
    }

    /** RSA-decrypt a Nagad response with the merchant's own private key. */
    private function decrypt(string $base64, PaymentMethod $method): array
    {
        if ($base64 === '') {
            return [];
        }

        openssl_private_decrypt(base64_decode($base64), $decrypted, (string) $method->configValue('merchant_private_key'));

        return json_decode((string) $decrypted, true) ?? [];
    }

    /** SHA256withRSA signature over the plaintext payload, using the merchant's private key. */
    private function sign(array $data, PaymentMethod $method): string
    {
        openssl_sign(json_encode($data), $signature, (string) $method->configValue('merchant_private_key'), OPENSSL_ALGO_SHA256);

        return base64_encode($signature);
    }

    private function headers(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'X-KM-IP-V4'   => request()->ip() ?: '127.0.0.1',
            'X-KM-Client-Type' => 'PC_WEB',
        ];
    }

    private function baseUrl(PaymentMethod $method): string
    {
        if ($override = $method->configValue('base_url')) {
            return rtrim($override, '/');
        }

        return $method->configValue('sandbox', true)
            ? 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0'
            : 'https://api.mynagad.com/remote-payment-gateway-1.0';
    }
}
