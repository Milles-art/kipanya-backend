<?php

namespace App\Http\Requests\Api\V1\Commerce;

use Illuminate\Foundation\Http\FormRequest;

final class WearProductIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['sometimes', 'string', 'max:40'],
            'featured' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }
}
