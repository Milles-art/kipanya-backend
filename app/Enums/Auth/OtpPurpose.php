<?php

namespace App\Enums\Auth;

enum OtpPurpose: string
{
    case Registration = 'registration';
    case Login = 'login';
    // Separate purpose so a storefront login code can never be replayed at the
    // administrator login (and vice versa), and the two flows cannot share a
    // single brute-force budget.
    case AdminLogin = 'admin_login';
    case PasswordReset = 'password_reset';
    case PhoneChange = 'phone_change';
}
