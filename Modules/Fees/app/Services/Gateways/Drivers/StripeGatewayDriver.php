<?php

namespace Modules\Fees\Services\Gateways\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Modules\Fees\Models\FeePaymentIntent;
use Modules\Fees\Services\Gateways\Contracts\PaymentGatewayDriver;
use Modules\Settings\Models\PaymentMethod;
use Stripe\Checkout\Session as StripeSession;
use Stripe\StripeClient;
use Stripe\Webhook as StripeWebhook;

/**
 * Real Stripe Checkout integration (stripe/stripe-php). Works fully in test
 * mode once a school enters their Stripe test (or live) secret/publishable
 * keys in the payment method's config — nothing here is a stub.
 *
 * Config keys (PaymentMethod.config): secret_key, publishable_key,
 * webhook_secret (optional — enables async confirmation), currency (default "usd";
 * Stripe does not support BDT as a presentment currency on most accounts, so
 * this is deliberately configurable rather than hardcoded).
 */
class StripeGatewayDriver implements PaymentGatewayDriver
{
    public function checkout(FeePaymentIntent $intent, PaymentMethod $method): string
    {
        $client = new StripeClient($method->configValue('secret_key'));

        $session = $client->checkout->sessions->create([
            'mode'                => 'payment',
            'line_items'          => [[
                'price_data' => [
                    'currency'     => $method->configValue('currency', 'usd'),
                    'unit_amount'  => (int) round(((float) $intent->amount) * 100),
                    'product_data' => ['name' => 'School Fee Payment — Ref '.$intent->token],
                ],
                'quantity' => 1,
            ]],
            'success_url'         => URL::route('portal.pay.return', ['token' => $intent->token]).'&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'          => URL::route('portal.pay.return', ['token' => $intent->token, 'cancelled' => 1]),
            'client_reference_id' => $intent->token,
            'metadata'            => ['intent_token' => $intent->token],
        ]);

        $intent->update(['gateway_reference' => $session->id]);

        return $session->url;
    }

    public function verify(FeePaymentIntent $intent, PaymentMethod $method, Request $request): array
    {
        $client = new StripeClient($method->configValue('secret_key'));

        // Webhook path: verify signature, then trust the event payload.
        if ($request->hasHeader('Stripe-Signature') && $method->configValue('webhook_secret')) {
            try {
                $event = StripeWebhook::constructEvent(
                    $request->getContent(),
                    $request->header('Stripe-Signature'),
                    $method->configValue('webhook_secret'),
                );
            } catch (\Throwable $e) {
                Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);

                return ['success' => false, 'gateway_reference' => null, 'message' => 'Invalid webhook signature.', 'raw' => []];
            }

            $session = $event->data->object;

            return [
                'success'           => $event->type === 'checkout.session.completed' && $session->payment_status === 'paid',
                'gateway_reference' => $session->payment_intent ?? $session->id,
                'message'           => null,
                'raw'               => $session->toArray(),
            ];
        }

        // Browser-return path: re-fetch the session by id and check its status directly.
        $sessionId = $request->query('session_id');
        if (! $sessionId) {
            return ['success' => false, 'gateway_reference' => null, 'message' => 'Missing session_id.', 'raw' => []];
        }

        $session = $client->checkout->sessions->retrieve($sessionId);

        return [
            'success'           => $session->payment_status === 'paid',
            'gateway_reference' => $session->payment_intent ?? $session->id,
            'message'           => $session->payment_status !== 'paid' ? 'Payment not completed.' : null,
            'raw'               => $session->toArray(),
        ];
    }
}
