<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Enums\Content\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CartoonRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->input('status', $this->route('cartoon')?->status?->value ?? ContentStatus::Draft->value),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('status') !== ContentStatus::Published->value) {
                return;
            }

            $cartoon = $this->route('cartoon');
            $hasExistingArtwork = $cartoon && ($cartoon->thumbnail_path || $cartoon->thumbnail_url);
            $hasNewArtwork = $this->hasFile('thumbnail') || filled($this->input('thumbnail_url'));

            if (! $hasExistingArtwork && ! $hasNewArtwork) {
                $validator->errors()->add('thumbnail', 'Published cartoons must have artwork.');
            }
        });
    }

    public function rules(): array
    {
        $id = $this->route('cartoon')?->id;
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('cartoons', 'slug')->ignore($id)],
            'description' => ['nullable', 'string'],
            'caption' => ['nullable', 'string', 'max:500'],
            'thumbnail_url' => ['nullable', 'url', 'max:2048'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'artwork_format' => ['sometimes', 'in:portrait,landscape,square'],
            'status' => ['sometimes', Rule::enum(ContentStatus::class)],
            'is_featured' => ['sometimes', 'boolean'],
            'is_daily' => ['sometimes', 'boolean'],
            'daily_date' => ['nullable', 'date'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
