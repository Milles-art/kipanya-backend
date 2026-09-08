<?php

namespace App\Enums\Auth;

enum OtpPurpose: string
{
    case Registration = 'registration';
    case Login = 'login';
    case PasswordReset = 'password_reset';
    case PhoneChange = 'phone_change';
}
