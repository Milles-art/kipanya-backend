<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Rules\ValidTanzanianPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class RequestRegistrationOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:30', new ValidTanzanianPhoneNumber()],
        ];
    }
}
