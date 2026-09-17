<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Enums\Auth\OtpPurpose;
use App\Rules\ValidTanzanianPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:30', new ValidTanzanianPhoneNumber()],
            'purpose' => ['required', Rule::enum(OtpPurpose::class)],
        ];
    }
}
