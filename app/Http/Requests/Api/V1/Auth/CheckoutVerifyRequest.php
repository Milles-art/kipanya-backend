<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Rules\ValidTanzanianPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutVerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:30', new ValidTanzanianPhoneNumber()],
            'code' => ['required', 'digits:6'],
            // Required only for new numbers; the controller enforces that once
            // it knows whether the phone belongs to an active account.
            'name' => ['nullable', 'string', 'min:2', 'max:80'],
        ];
    }
}
