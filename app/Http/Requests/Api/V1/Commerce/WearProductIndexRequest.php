<?php

namespace App\Http\Requests\Api\V1\Commerce;

use Illuminate\Foundation\Http\FormRequest;

final class WearProductIndexRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'category' => ['sometimes', 'string', 'max:40'],
            'featured' => ['sometimes', 'boolean'],
            'q' => ['sometimes', 'string', 'max:100'],
            'price_min' => ['sometimes', 'numeric', 'min:0'],
            'price_max' => ['sometimes', 'numeric', 'min:0'],
            'sale' => ['sometimes', 'boolean'],
            'sort' => ['sometimes', 'in:featured,price-asc,price-desc,newest'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->filled('price_min') && $this->filled('price_max') && (float) $this->input('price_min') > (float) $this->input('price_max')) {
                $validator->errors()->add('price_min', 'Minimum price cannot exceed maximum price.');
            }
        });
    }
}
