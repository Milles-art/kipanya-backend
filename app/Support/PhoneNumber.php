<?php

namespace App\Support;

use InvalidArgumentException;

final class PhoneNumber
{
    private function __construct(private readonly string $value) {}

    public static function normalize(string $phone): self
    {
        $value = preg_replace('/[\s().-]+/', '', trim($phone)) ?? '';

        if (str_starts_with($value, '00')) {
            $value = '+' . substr($value, 2);
        } elseif (str_starts_with($value, '0')) {
            $value = '+255' . substr($value, 1);
        } elseif (preg_match('/^255\d{9}$/', $value)) {
            $value = '+' . $value;
        }

        if (!preg_match('/^\+255\d{9}$/', $value)) {
            throw new InvalidArgumentException('Invalid Tanzanian phone number.');
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
