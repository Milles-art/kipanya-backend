<?php

namespace App\DTOs\Auth;

final readonly class RegisterUserData
{
    public function __construct(
        public string $name,
        public string $phone,
        public ?string $referralCode = null,
    ) {}
}
