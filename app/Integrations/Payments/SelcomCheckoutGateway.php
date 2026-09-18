<?php

namespace App\Integrations\Payments;

use App\Models\Wear\WearOrder;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Selcom Checkout API (developers.selcommobile.com) payment gateway.
 *
 * Authentication: every request carries a signed set of headers:
 *   Authorization: SELCOM <Base64(api_key)>
 *   Timestamp:     ISO 8601 timestamp with timezone offset
 *   Digest-Method: HS256
 *   Digest:        Base64(HMAC-SHA256(signing_string, api_secret))
 *   Signed-Fields: comma-separated fields included in the signing string
 *
 * The signing string begins with timestamp=<timestamp> followed by each
 * signed field in the exact order listed in Signed-Fields.
 */
final class SelcomCheckoutGateway implements PaymentGateway
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
        private readonly string $apiSecret,
        private readonly string $vendorId,
        private readonly string $currency,
        private readonly string $redirectUrl,
        private readonly string $cancelUrl,
        private readonly ?string $webhookUrl,
        private readonly int $timeout,
    ) {}

    public function initiate(WearOrder $order, string $idempotencyKey): array
    {
        if ($this->redirectUrl === '' || $this->cancelUrl === '') {
            throw new RuntimeException(
                'Selcom Checkout redirect_url and cancel_url must be configured (SELCOM_REDIRECT_URL / SELCOM_CANCEL_URL).'
            );
        }

        $orderId = $order->order_number;
        $webhook = $this->webhookUrl ?: (string) url()->route('selcom.webhook');

        $payload = [
            'vendor' => $this->vendorId,
            'order_id' => $orderId,
            'buyer_email' => (string) $order->customer_email,
            'buyer_name' => (string) $order->customer_name,
            'buyer_phone' => ltrim((string) $order->customer_phone, '+'),
            'amount' => (string) ((int) round((float) $order->total)),
            'currency' => $this->currency,
            'redirect_url' => base64_encode($this->redirectUrl),
            'cancel_url' => base64_encode($this->cancelUrl),
            'webhook' => base64_encode($webhook),
            'buyer_remarks' => 'Kipanya order '.$orderId,
            'merchant_remarks' => 'Kipanya order '.$orderId,
        ];

        $itemCount = $order->items()->count();
        if ($itemCount > 0) {
            $payload['no_of_items'] = (string) $itemCount;
        }

        $timestamp = $this->timestamp();

        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->withHeaders($this->headers($timestamp, array_keys($payload), $payload))
            ->acceptJson()
            ->asJson()
            ->post('/v1/checkout/create-order-minimal', $payload);

        if (! $response->successful()) {
            throw $this->failedResponseException($response);
        }

        $data = $response->json();
        $reference = $data['reference'] ?? null;
        $gateway = isset($data['data'][0]) && is_array($data['data'][0]) ? $data['data'][0] : [];
        $encodedUrl = $gateway['payment_gateway_url'] ?? null;
        $gatewayUrl = is_string($encodedUrl) ? (base64_decode($encodedUrl, true) ?: null) : null;

        if (! is_string($reference) || $reference === '') {
            throw new RuntimeException('Selcom Checkout did not return a merchant reference.');
        }

        if (! is_string($gatewayUrl) || $gatewayUrl === '') {
            throw new RuntimeException('Selcom Checkout did not return a payment gateway URL.');
        }

        return [
            'provider' => 'selcom_checkout',
            'reference' => $reference,
            'status' => 'pending',
            'payload' => [
                'gateway' => 'selcom_checkout',
                'gateway_order_id' => $orderId,
                'payment_gateway_url' => $gatewayUrl,
                'gateway_buyer_uuid' => $gateway['gateway_buyer_uuid'] ?? null,
                'payment_token' => $gateway['payment_token'] ?? null,
                'provider_reference' => $reference,
            ],
        ];
    }

    public function status(string $orderId): array
    {
        $timestamp = $this->timestamp();
        $query = ['order_id' => $orderId];

        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->withHeaders($this->headers($timestamp, array_keys($query), $query))
            ->acceptJson()
            ->get('/v1/checkout/order-status?'.http_build_query($query));

        if (! $response->successful()) {
            throw $this->failedResponseException($response);
        }

        $body = $response->json();
        $record = $this->statusRecord($body);

        return [
            'status' => $this->normalizeStatus((string) ($record['payment_status'] ?? 'PENDING')),
            'provider' => 'selcom_checkout',
            'transid' => $record['transid'] ?? null,
            'reference' => $record['reference'] ?? null,
            'channel' => $record['channel'] ?? null,
            'amount' => $record['amount'] ?? null,
            'payload' => $record,
        ];
    }

    public function cancel(string $orderId): bool
    {
        $timestamp = $this->timestamp();
        $query = ['order_id' => $orderId];

        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->withHeaders($this->headers($timestamp, array_keys($query), $query))
            ->acceptJson()
            ->delete('/v1/checkout/cancel-order?'.http_build_query($query));

        if (! $response->successful()) {
            throw $this->failedResponseException($response);
        }

        return true;
    }

    private function headers(string $timestamp, array $fieldNames, array $signedValues): array
    {
        return [
            'Authorization' => 'SELCOM '.base64_encode($this->apiKey),
            'Timestamp' => $timestamp,
            'Digest-Method' => 'HS256',
            'Digest' => $this->signature($timestamp, $signedValues),
            'Signed-Fields' => implode(',', array_values($fieldNames)),
            'Accept' => 'application/json',
        ];
    }

    private function signature(string $timestamp, array $fields): string
    {
        $parts = ['timestamp='.$timestamp];

        foreach ($fields as $key => $value) {
            $parts[] = $key.'='.(string) $value;
        }

        return base64_encode(hash_hmac('sha256', implode('&', $parts), $this->apiSecret, true));
    }

    private function timestamp(): string
    {
        return now()->format('Y-m-d\TH:i:sP');
    }

    private function statusRecord(array $body): array
    {
        $data = $body['data'] ?? $body;

        if (is_array($data) && array_key_exists(0, $data) && is_array($data[0])) {
            return $data[0];
        }

        if (is_array($data) && isset($data['order']) && is_array($data['order'])) {
            return $data['order'];
        }

        return is_array($data) ? $data : [];
    }

    private function normalizeStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'COMPLETED' => 'completed',
            'CANCELLED' => 'cancelled',
            'USERCANCELED', 'USERCANCELLED' => 'user_cancelled',
            'REJECTED' => 'rejected',
            'INPROGRESS' => 'in_progress',
            'PENDING' => 'pending',
            default => 'pending',
        };
    }

    /**
     * Build a failure exception that never includes the api key, api secret or
     * request digest. Only the HTTP status and safe provider fields are used.
     */
    private function failedResponseException(Response $response): RuntimeException
    {
        $body = $response->json();
        $detail = '';

        if (is_array($body)) {
            $detail = trim(implode(' ', array_filter([
                $body['resultcode'] ?? null,
                $body['result'] ?? null,
                $body['message'] ?? null,
            ], fn ($value): bool => is_string($value) && $value !== '')));
        }

        $message = 'Selcom Checkout request failed with HTTP '.$response->status();
        if ($detail !== '') {
            $message .= ' ('.$detail.')';
        }

        return new RuntimeException($message);
    }
}
