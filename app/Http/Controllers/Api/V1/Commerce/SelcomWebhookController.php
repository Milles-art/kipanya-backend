<?php

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Commerce\PaymentTransaction;
use App\Models\SelcomWebhookLog;
use App\Services\Payments\PaymentService;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Arr;
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

        $orderId = is_scalar($payload['order_id'] ?? null) ? (string) $payload['order_id'] : '';
        $reference = is_scalar($payload['reference'] ?? null) ? (string) $payload['reference'] : null;
        $rawTransid = is_scalar($payload['transid'] ?? null) ? trim((string) $payload['transid']) : '';

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

        // Idempotency key. Use the provider's transaction id; a callback without one
        // must still be de-duplicated, so derive a stable key from what identifies it.
        $transid = $rawTransid !== ''
            ? mb_substr($rawTransid, 0, 100)
            : 'nt_'.hash('sha256', implode('|', [$orderId, (string) $reference, (string) ($payload['payment_status'] ?? '')]));

        // Replay protection: if this callback was already processed, answer OK again.
        $alreadyProcessed = SelcomWebhookLog::query()
            ->where('transid', $transid)
            ->where('processed_successfully', true)
            ->exists();
        if ($alreadyProcessed) {
            return response()->json(['verified' => true, 'result' => 'OK', 'duplicate' => true]);
        }

        try {
            $providerState = [
                'status' => (string) ($payload['payment_status'] ?? ''),
                'transid' => $rawTransid !== '' ? $rawTransid : null,
                'reference' => $payment->provider_reference,
                'channel' => $payload['channel'] ?? null,
                'amount' => $payload['amount'] ?? null,
                'currency' => $payload['currency'] ?? null,
                'payload' => $payload,
            ];

            $this->payments->applyProviderStatus($payment, $providerState);

            $this->recordCallback($transid, $orderId, $payment, $payload, true, null);

            return response()->json(['verified' => true, 'result' => 'OK']);
        } catch (ValidationException $e) {
            // Log failed validation but don't retry
            $this->recordCallback($transid, $orderId, $payment, $payload, false, $e->getMessage());

            return response()->json([
                'verified' => true,
                'error' => 'invalid_callback',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Upsert the callback log. `transid` is unique, so a plain create() crashed
     * with a 500 whenever the provider re-delivered a callback (after a 422, or
     * concurrently). updateOrCreate + a duplicate-key retry makes logging idempotent.
     */
    private function recordCallback(
        string $transid,
        string $orderId,
        PaymentTransaction $payment,
        array $payload,
        bool $processed,
        ?string $error,
    ): void {
        $attributes = [
            'order_id' => mb_substr($orderId, 0, 50),
            'payment_transaction_id' => $payment->id,
            'payment_status' => mb_substr((string) ($payload['payment_status'] ?? 'unknown'), 0, 30),
            'processed_successfully' => $processed,
            'error' => $error,
            // Keep the audit trail but do not store the payer's personal details.
            'raw_payload' => Arr::except($payload, ['msisdn', 'phone', 'buyer_phone', 'buyer_email', 'buyer_name', 'email', 'name']),
        ];

        try {
            SelcomWebhookLog::query()->updateOrCreate(['transid' => $transid], $attributes);
        } catch (UniqueConstraintViolationException) {
            // A concurrent delivery inserted the row first.
            SelcomWebhookLog::query()->where('transid', $transid)->update(
                Arr::except($attributes, ['raw_payload']) + ['updated_at' => now()],
            );
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

        $fields = array_map('trim', explode(',', $signedFields));

        // The identity and the status of the payment must be covered by the signature,
        // otherwise an unsigned field could be altered on a captured, valid callback.
        foreach (['order_id', 'payment_status'] as $required) {
            if (! in_array($required, $fields, true)) {
                return false;
            }
        }

        foreach ($fields as $field) {
            // Non-scalar values cannot be signed and would raise "Array to string".
            if (! array_key_exists($field, $body) || ! is_scalar($body[$field])) {
                return false;
            }

            $parts[] = $field.'='.(string) $body[$field];
        }

        $expected = base64_encode(hash_hmac('sha256', implode('&', $parts), $secret, true));

        return hash_equals($expected, $digest);
    }
}
