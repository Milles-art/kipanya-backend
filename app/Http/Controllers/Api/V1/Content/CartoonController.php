<?php

namespace App\Http\Controllers\Api\V1\Content;

use App\Enums\Content\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CartoonResource;
use App\Models\Content\Cartoon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class CartoonController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Cartoon::query()
            ->with('category')
            ->where('status', ContentStatus::Published->value)
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('category', fn ($category) =>
                    $category->where('slug', $request->string('category')->toString())
                );
            })
            ->when($request->boolean('featured'), fn ($query) => $query->where('is_featured', true))
            ->orderByDesc('published_at')
            ->orderBy('sort_order');

        return CartoonResource::collection($query->paginate(20));
    }

    public function show(Cartoon $cartoon): CartoonResource
    {
        abort_unless($cartoon->status === ContentStatus::Published, 404);

        $cartoon->load([
            'category',
            'episodes' => fn ($query) => $query
                ->where('status', ContentStatus::Published->value)
                ->orderBy('episode_number'),
        ]);

        return new CartoonResource($cartoon);
    }

    public function favorite(Request $request, Cartoon $cartoon): JsonResponse
    {
        abort_unless($cartoon->status === ContentStatus::Published, 404);

        $request->user()->favorites()->syncWithoutDetaching([$cartoon->id]);

        return response()->json(['message' => 'Added to favorites.']);
    }

    public function unfavorite(Request $request, Cartoon $cartoon): JsonResponse
    {
        $request->user()->favorites()->detach($cartoon->id);

        return response()->json(['message' => 'Removed from favorites.']);
    }
}
