<?php

namespace App\Support;

/**
 * Removes payer personal data from provider callbacks before they are stored on a payment.
 * Keeps identifiers needed for reconciliation (transaction id, reference, status, amount).
 */
final class PaymentPayloadSanitizer
{
    private const SENSITIVE_KEY = '/(^|_)(msisdn|phone|mobile|email|name|address|pan|card|cvv|password|secret)($|_)/i';

    public static function redact(array $data): array
    {
        $clean = [];

        foreach ($data as $key => $value) {
            if (is_string($key) && preg_match(self::SENSITIVE_KEY, $key) === 1) {
                continue;
            }

            $clean[$key] = is_array($value) ? self::redact($value) : $value;
        }

        return $clean;
    }
}
