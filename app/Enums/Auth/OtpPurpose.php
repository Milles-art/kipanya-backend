<?php

namespace App\Enums\Auth;

/**
 * Purposes for the OTP flows that remain after the password migration:
 * changing the contact details of an EXISTING account. Storefront sign-in,
 * registration and guest checkout are password-based and never send codes.
 */
enum OtpPurpose: string
{
    case PhoneChange = 'phone_change';
    // Email changes are confirmed with a code sent to the account's verified
    // phone number, so control of the (possibly unverified) new address alone
    // can never redirect the account's recovery channel.
    case EmailChange = 'email_change';
}
