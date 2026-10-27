<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Rules\ValidTanzanianPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

/**
 * One-step guest-checkout sign-in / account creation: phone + password, plus
 * a name used only when the number is new. Replaces the old two-step SMS code
 * exchange; no verification code is involved anywhere in this flow.
 */
class CheckoutSigninRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:30', new ValidTanzanianPhoneNumber()],
            // Ignored for known numbers; required (min length) by the controller
            // only when the number creates a new account.
            'name' => ['nullable', 'string', 'max:80'],
            'password' => ['required', 'string', 'min:8', 'max:200'],
        ];
    }
}
