<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CollectionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('collection')?->id;
        return [
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:200', Rule::unique('collections', 'slug')->ignore($id)],
            'description' => ['nullable', 'string'],
            'cover_url' => ['nullable', 'url', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
