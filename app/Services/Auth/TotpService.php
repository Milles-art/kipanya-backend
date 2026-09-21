<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Cache;

/**
 * Minimal RFC 6238 / RFC 4226 (HOTP) implementation used as a second factor
 * for administrator accounts. Codes are 6 digits, SHA-1, 30 second period,
 * accepting one step of clock drift in either direction.
 */
final class TotpService
{
    private const DIGITS = 6;

    private const PERIOD = 30;

    private const WINDOW = 1;

    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(int $length = 32): string
    {
        $bytes = random_bytes($length);

        return $this->base32Encode($bytes);
    }

    public function provisioningUri(string $secret, string $issuer, string $account): string
    {
        $label = rawurlencode($issuer.':'.$account);
        $issuerPart = rawurlencode($issuer);
        $params = [
            'secret' => $secret,
            'issuer' => $issuer,
            'digits' => self::DIGITS,
            'period' => self::PERIOD,
            'algorithm' => 'SHA1',
        ];

        return "otpauth://totp/{$label}?".http_build_query($params);
    }

    public function code(string $secret, int $window = 0): string
    {
        $counter = (int) floor(time() / self::PERIOD) + $window;

        return $this->codeForCounter($secret, $counter);
    }

    /** Functional time-based code for an exact epoch counter (RFC tests). */
    public function codeForCounter(string $secret, int $counter): string
    {
        return $this->hotp($secret, $counter);
    }

    public function verify(string $secret, string $code, int $window = self::WINDOW): bool
    {
        $code = trim($code);

        if (! preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        if (mb_strlen($secret) < 16) {
            return false;
        }

        for ($offset = -$window; $offset <= $window; $offset++) {
            if (hash_equals($this->code($secret, $offset), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify and consume a code in a single step, rejecting any code whose
     * time counter has already been used. Callers granting access must use
     * this instead of verify() so a captured code cannot be replayed during
     * the +/-1 window.
     */
    public function verifyAndConsume(string $secret, string $code, int $window = self::WINDOW): bool
    {
        $consumedKey = $this->consumedKey($secret);
        $lastConsumed = (int) Cache::get($consumedKey, 0);

        $counter = $this->matchingCounter($secret, $code, $window);

        if ($counter === null || $counter <= $lastConsumed) {
            return false;
        }

        Cache::put($consumedKey, $counter, now()->addSeconds(self::PERIOD * ($window * 2 + 2)));

        return true;
    }

    public function wasConsumed(string $secret, string $code, int $window = self::WINDOW): bool
    {
        $counter = $this->matchingCounter($secret, $code, $window);

        return $counter !== null
            && $counter <= (int) Cache::get($this->consumedKey($secret), 0);
    }

    private function matchingCounter(string $secret, string $code, int $window): ?int
    {
        $code = trim($code);

        if (! preg_match('/^\d{6}$/', $code) || mb_strlen($secret) < 16) {
            return null;
        }

        $current = (int) floor(time() / self::PERIOD);
        $offsets = [0];
        for ($step = 1; $step <= $window; $step++) {
            $offsets[] = -$step;
            $offsets[] = $step;
        }

        foreach ($offsets as $offset) {
            if (hash_equals($this->codeForCounter($secret, $current + $offset), $code)) {
                return $current + $offset;
            }
        }

        return null;
    }

    private function consumedKey(string $secret): string
    {
        return 'totp:consumed:'.sha1($secret);
    }

    private function hotp(string $secret, int $counter): string
    {
        $key = $this->base32Decode($secret);
        $counterBytes = pack('N*', 0, $counter);

        $hash = hash_hmac('sha1', $counterBytes, $key, true);

        // Dynamic truncation per RFC 4226 section 5.3.
        $offset = ord($hash[19]) & 0x0F;
        $value = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($value % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $data): string
    {
        $binary = '';
        foreach (str_split($data) as $byte) {
            $binary .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';
        foreach (str_split($binary, 5) as $chunk) {
            $encoded .= self::BASE32_ALPHABET[bindec(str_pad($chunk, 5, '0'))];
        }

        return $encoded;
    }

    private function base32Decode(string $data): string
    {
        $data = strtoupper(rtrim($data, '='));

        $bits = '';
        foreach (str_split($data) as $char) {
            $value = strpos(self::BASE32_ALPHABET, $char);

            if ($value === false) {
                return '';
            }

            $bits .= str_pad(decbin($value), 5, '0', STR_PAD_LEFT);
        }

        $decoded = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $decoded .= chr(bindec($chunk));
            }
        }

        return $decoded;
    }
}
