<?php

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Commerce\PaymentTransaction;
use App\Models\SelcomWebhookLog;
use App\Services\Payments\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class SelcomWebhookController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function handle(Request $request): JsonResponse
    {
        if (! $request->isJson() || ! $this->verifySignature($request)) {
            return response()->json(['verified' => false, 'error' => 'invalid_signature'], 401);
        }

        // Timestamp freshness check (prevent replay attacks)
        if (! $this->verifyTimestampFreshness($request)) {
            return response()->json(['verified' => false, 'error' => 'timestamp_expired'], 401);
        }

        $payload = $request->json()->all();

        $orderId = (string) ($payload['order_id'] ?? '');
        $reference = $payload['reference'] ?? null;
        $transid = $payload['transid'] ?? null;

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

        // Replay protection: if this transid was already processed, return success idempotently
        if ($transid !== null) {
            $alreadyProcessed = SelcomWebhookLog::query()
                ->where('transid', $transid)
                ->where('processed_successfully', true)
                ->exists();
            if ($alreadyProcessed) {
                return response()->json(['verified' => true, 'result' => 'OK', 'duplicate' => true]);
            }
        }

        try {
            $providerState = [
                'status' => (string) ($payload['payment_status'] ?? ''),
                'transid' => $transid,
                'reference' => $payment->provider_reference,
                'channel' => $payload['channel'] ?? null,
                'amount' => $payload['amount'] ?? null,
                'currency' => $payload['currency'] ?? null,
                'payload' => $payload,
            ];

            $result = $this->payments->applyProviderStatus($payment, $providerState);

            // Log successful webhook processing
            if ($transid !== null) {
                SelcomWebhookLog::query()->create([
                    'transid' => $transid,
                    'order_id' => $orderId,
                    'payment_transaction_id' => $payment->id,
                    'payment_status' => $payload['payment_status'] ?? 'unknown',
                    'processed_successfully' => true,
                    'raw_payload' => $payload,
                ]);
            }

            return response()->json(['verified' => true, 'result' => 'OK']);
        } catch (ValidationException $e) {
            // Log failed validation but don't retry
            if ($transid !== null) {
                SelcomWebhookLog::query()->create([
                    'transid' => $transid,
                    'order_id' => $orderId,
                    'payment_transaction_id' => $payment->id,
                    'payment_status' => $payload['payment_status'] ?? 'unknown',
                    'processed_successfully' => false,
                    'error' => $e->getMessage(),
                    'raw_payload' => $payload,
                ]);
            }

            return response()->json([
                'verified' => true,
                'error' => 'invalid_callback',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    private function verifyTimestampFreshness(Request $request): bool
    {
        $timestampHeader = $request->header('Timestamp');

        if (! is_string($timestampHeader) || $timestampHeader === '') {
            return false;
        }

        try {
            // Parse timestamp in UTC to ensure consistent comparison
            $timestamp = Carbon::parse($timestampHeader, 'UTC');
        } catch (\Throwable) {
            return false;
        }

        // Use UTC now for consistent comparison
        $now = Carbon::now('UTC');

        // Allow up to 10 minutes in the past and 2 minutes in the future (clock skew)
        $maxAge = 10 * 60; // 10 minutes
        $maxFuture = 2 * 60; // 2 minutes

        $age = $now->diffInSeconds($timestamp, absolute: true);
        $isFuture = $timestamp->gt($now);

        if ($isFuture && $age > $maxFuture) {
            return false; // Timestamp too far in the future
        }

        if (! $isFuture && $age > $maxAge) {
            return false; // Timestamp too old
        }

        return true;
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
