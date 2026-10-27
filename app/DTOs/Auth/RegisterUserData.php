<?php

namespace App\DTOs\Auth;

final readonly class RegisterUserData
{
    public function __construct(
        public string $name,
        public string $phone,
        public ?string $referralCode = null,
        // Plaintext: hashed by the User model's `hashed` cast on save. null
        // keeps the legacy "no password yet" state available (admin-created
        // accounts), storefront sign-up always supplies one.
        public ?string $password = null,
        public ?string $email = null,
        // HMAC of the kp_device cookie and the creating connection's IP —
        // anti-farm signals shown in the admin panel.
        public ?string $deviceHash = null,
        public ?string $signupIp = null,
    ) {}
}
