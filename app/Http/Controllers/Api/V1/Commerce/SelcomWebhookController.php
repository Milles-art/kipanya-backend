<?php

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Commerce\PaymentTransaction;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Receives Selcom Checkout payment callbacks. Selcom signs callbacks with the
 * same SELCOM headers (Authorization, Timestamp, Digest-Method, Digest and
 * Signed-Fields) used for the Checkout API, and the signature is verified
 * before any payment state change is attempted.
 */
final class SelcomWebhookController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function handle(Request $request): JsonResponse
    {
        if (! $request->isJson() || ! $this->verifySignature($request)) {
            return response()->json(['verified' => false, 'error' => 'invalid_signature'], 401);
        }

        $payload = $request->json()->all();

        $orderId = (string) ($payload['order_id'] ?? '');
        $reference = $payload['reference'] ?? null;

        $payment = PaymentTransaction::query()
            ->whereHas('order', fn ($query) => $query->where('order_number', $orderId))
            ->with('order')
            ->first();

        if (! $payment) {
            return response()->json(['verified' => true, 'error' => 'order_not_found'], 404);
        }

        // The Selcom merchant reference must match the reference stored at
        // initiation, so a callback can never settle a different order.
        if ($reference !== null && (string) $payment->provider_reference !== (string) $reference) {
            return response()->json(['verified' => true, 'error' => 'reference_mismatch'], 404);
        }

        try {
            $this->payments->applyProviderStatus($payment, [
                'status' => (string) ($payload['payment_status'] ?? ''),
                'transid' => $payload['transid'] ?? null,
                'reference' => $payment->provider_reference,
                'channel' => $payload['channel'] ?? null,
                'amount' => $payload['amount'] ?? null,
                'currency' => $payload['currency'] ?? null,
                'payload' => $payload,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'verified' => true,
                'error' => 'invalid_callback',
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json(['verified' => true, 'result' => 'OK']);
    }

    private function verifySignature(Request $request): bool
    {
        $key = config('services.selcom.api_key');
        $secret = config('services.selcom.api_secret');

        if (! is_string($key) || trim($key) === '' || ! is_string($secret) || trim($secret) === '') {
            return false;
        }

        $timestamp = $request->header('Timestamp');
        $digestMethod = $request->header('Digest-Method');
        $digest = $request->header('Digest');
        $authorization = $request->header('Authorization');
        $signedFields = $request->header('Signed-Fields');

        if (! is_string($timestamp) || $timestamp === ''
            || ! is_string($digest) || $digest === ''
            || ! is_string($signedFields) || $signedFields === '') {
            return false;
        }

        if (! is_string($digestMethod) || strtoupper($digestMethod) !== 'HS256') {
            return false;
        }

        if (! is_string($authorization) || ! hash_equals('SELCOM '.base64_encode($key), $authorization)) {
            return false;
        }

        $body = $request->json()->all();
        $parts = ['timestamp='.$timestamp];

        foreach (explode(',', $signedFields) as $field) {
            $field = trim($field);

            if (! array_key_exists($field, $body)) {
                return false;
            }

            $parts[] = $field.'='.(string) $body[$field];
        }

        $expected = base64_encode(hash_hmac('sha256', implode('&', $parts), $secret, true));

        return hash_equals($expected, $digest);
    }
}
