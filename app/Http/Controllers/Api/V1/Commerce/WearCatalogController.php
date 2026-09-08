<?php

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Commerce\WearProductIndexRequest;
use App\Http\Resources\Api\V1\Commerce\WearProductResource;
use App\Models\Wear\WearProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

final class WearCatalogController extends Controller
{
    public function index(WearProductIndexRequest $request): AnonymousResourceCollection
    {
        $products = WearProduct::query()
            ->with('variants')
            ->where('is_active', true)
            ->when(
                $request->filled('category'),
                fn ($query) => $query->where('category', $request->string('category')->toString())
            )
            ->when(
                $request->boolean('featured'),
                fn ($query) => $query->where('is_featured', true)
            )
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($request->integer('per_page', 20));

        return WearProductResource::collection($products);
    }

    public function categories(): JsonResponse
    {
        $categories = WearProduct::query()
            ->where('is_active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->map(fn (string $category) => [
                'name' => $category,
                'slug' => Str::slug($category),
            ])
            ->values();

        return response()->json(['data' => $categories]);
    }

    public function show(WearProduct $product): WearProductResource
    {
        abort_unless($product->is_active, 404);

        $product->load('variants');

        return new WearProductResource($product);
    }
}
