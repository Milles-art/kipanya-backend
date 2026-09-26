<?php

namespace App\Http\Requests\Api\V1\Commerce;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidTanzanianPhoneNumber;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'address_id' => [
                'nullable',
                'integer',
                Rule::exists('addresses', 'id')->where(fn ($query) => $query
                    ->where('user_id', $this->user()?->id)
                    ->where('type', 'shipping')
                ),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['nullable', 'string', Rule::in(['mobile_money', 'card'])],
            'payment_provider' => ['nullable', 'string', 'max:50', Rule::in(['mpesa', 'tigopesa', 'halopesa', 'airtelmoney'])],
            'payment_phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s-]{7,20}$/', new ValidTanzanianPhoneNumber],
        ];
    }
}
