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
    // Email changes are confirmed with a code sent to the account's verified
    // phone number, because this deployment has no mail transport that could
    // deliver a verification link (MAIL_MAILER=log).
    case EmailChange = 'email_change';
}
