<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Enums\Content\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EpisodeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'youtube_video_id' => ['nullable', 'string', 'max:50'],
            'youtube_url' => ['nullable', 'url', 'max:2048'],
            'thumbnail_url' => ['nullable', 'url', 'max:2048'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'episode_number' => ['required', 'integer', 'min:1'],
            'status' => ['sometimes', Rule::enum(ContentStatus::class)],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
