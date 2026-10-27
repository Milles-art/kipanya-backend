<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Rules\ValidTanzanianPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:30', new ValidTanzanianPhoneNumber()],
            'name' => ['required', 'string', 'min:2', 'max:80'],
            // Confirmation prevents a typo from locking a brand-new account out
            // before the customer has any recovery channel set up.
            'password' => ['required', 'string', 'min:8', 'max:200', 'confirmed'],
            // Optional: only used to send password-reset links. Encouraged on
            // the form because it is the account's sole self-serve recovery
            // channel, but never required to shop.
            'email' => ['nullable', 'email', 'max:255'],
            'referral_code' => ['nullable', 'string', 'max:40'],
        ];
    }
}
