<?php

namespace App\Support;

use App\Http\Middleware\EnsureDeviceCookie;
use Illuminate\Http\Request;

/**
 * Reads the first-party device id and derives the value stored on user rows.
 *
 * The database only ever holds the HMAC: a leaked user table cannot be
 * correlated back to live cookies, and two rows sharing a device_hash provably
 * came from the same browser without anyone being able to reproduce the id.
 */
final class DeviceIdentity
{
    /** Raw cookie value for this request, or null when not yet issued. */
    public static function id(Request $request): ?string
    {
        $raw = $request->cookie(EnsureDeviceCookie::COOKIE);

        return is_string($raw) && $raw !== '' ? $raw : null;
    }

    /** HMAC of the device id — the value stored in users.device_hash. */
    public static function hash(?string $deviceId): ?string
    {
        return $deviceId === null
            ? null
            : hash_hmac('sha256', $deviceId, (string) config('app.key'));
    }
}
