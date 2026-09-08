<?php

namespace App\Http\Requests\Api\V1\Commerce;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
