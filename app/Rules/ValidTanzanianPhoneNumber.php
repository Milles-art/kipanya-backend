<?php

namespace App\Rules;

use App\Support\PhoneNumber;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;

/**
 * Validates a phone number using the exact same normalization rules the
 * application applies, so callers can never reach PhoneNumber::normalize()
 * with an unparseable value and trigger an unhandled exception.
 */
final class ValidTanzanianPhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            $fail('The :attribute must be a valid Tanzanian phone number.');

            return;
        }

        try {
            PhoneNumber::normalize($value);
        } catch (InvalidArgumentException) {
            $fail('The :attribute must be a valid Tanzanian phone number.');
        }
    }
}
