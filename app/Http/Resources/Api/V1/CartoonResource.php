<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartoonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'thumbnail_url' => $this->resolved_thumbnail_url,
            'status' => $this->status?->value,
            'is_featured' => $this->is_featured,
            'published_at' => $this->published_at,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'episodes' => $this->whenLoaded('episodes', fn () => $this->episodes->map(fn ($episode) => [
                'id' => $episode->id,
                'title' => $episode->title,
                'slug' => $episode->slug,
                'description' => $episode->description,
                'episode_number' => $episode->episode_number,
                'youtube_video_id' => $episode->youtube_video_id,
                'youtube_url' => $episode->youtube_url,
                'thumbnail_url' => $episode->thumbnail_url,
                'duration_seconds' => $episode->duration_seconds,
                'published_at' => $episode->published_at,
            ])),
        ];
    }
}
