<?php

namespace App\Integrations\Sms;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Real SMS delivery through the Notify Africa Developer API
 * (https://docs.notify.africa/docs/api/sms). Deliberately does not depend on
 * the Notify Africa PHP SDK: it speaks the documented REST contract with
 * Laravel's built-in HTTP client.
 *
 * The send payload never holds anything more than the recipient and the OTP
 * message, and this gateway performs no logging, so neither the API key nor the
 * OTP can leak into responses or logs through this class.
 */
final class NotifyAfricaSmsGateway implements SmsGateway
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
        private readonly string $senderId,
        private readonly int $timeout,
    ) {
        if ($this->senderId === '') {
            throw new RuntimeException('Notify Africa SMS sender ID is not configured.');
        }
    }

    public function send(string $phone, string $message): void
    {
        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->withToken($this->apiKey)
            ->acceptJson()
            ->asJson()
            ->post('/api/v1/api/messages/send', [
                // Compatible valid-phonenumber field: international number with
                // an optional leading "+" (our canonical storefront numbers are
                // E.164 like +255712345678).
                'phone_number' => $phone,
                'message' => $message,
                'sender_id' => $this->senderId,
            ]);

        if (! $response->successful()) {
            // Surface only the HTTP status. The API's response body and our own
            // message could echo an OTP, and the key must never leave the
            // server, so neither is placed on the exception.
            throw new RuntimeException(sprintf(
                'Notify Africa SMS send failed (HTTP %d).',
                $response->status(),
            ));
        }
    }
}
