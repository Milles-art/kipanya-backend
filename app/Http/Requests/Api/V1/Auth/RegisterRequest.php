<?php

namespace App\Http\Requests\Api\V1\Auth;

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
            'phone' => ['required', 'string', 'max:30'],
            'code' => ['required', 'digits:6'],
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'referral_code' => ['nullable', 'string', 'max:40'],
        ];
    }
}
