<?php

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Commerce\WearProductIndexRequest;
use App\Http\Resources\Api\V1\Commerce\WearProductResource;
use App\Http\Resources\Api\V1\Commerce\WearCollectionResource;
use App\Models\Wear\WearCollection;
use App\Models\Wear\WearProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

final class WearCatalogController extends Controller
{
    public function index(WearProductIndexRequest $request): AnonymousResourceCollection
    {
        $products = WearProduct::query()
            ->with('variants')
            ->where('is_active', true)
            ->when(
                $request->filled('category'),
                function (Builder $query) use ($request): void {
                    $requested = $request->string('category')->trim()->toString();
                    $query->where(function (Builder $categoryQuery) use ($requested): void {
                        $categoryQuery
                            ->where('category', $requested)
                            ->orWhereRaw("LOWER(REPLACE(category, ' ', '-')) = ?", [Str::lower($requested)]);
                    });
                }
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

    public function collections(): AnonymousResourceCollection
    {
        $collections = WearCollection::query()
            ->where('is_active', true)
            ->withCount(['products' => fn (Builder $query) => $query->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return WearCollectionResource::collection($collections);
    }

    public function collection(WearCollection $collection): WearCollectionResource
    {
        abort_unless($collection->is_active, 404);

        // Load the many-to-many relation without a constrained eager-load closure.
        // This keeps the endpoint compatible with the current Laravel relationship
        // loader and avoids passing the BelongsToMany relation into a Builder-typed
        // callback. Filter inactive products after eager loading.
        $collection->load(['products.variants']);
        $collection->setRelation(
            'products',
            $collection->products->where('is_active', true)->values(),
        );

        return new WearCollectionResource($collection);
    }

    public function categories(): JsonResponse
    {
        // Wear currently supports these five customer-facing categories.
        // Keep this API contract stable even when one category temporarily has no products.
        $categories = collect([
            'Hoodies',
            'Long Sleeves',
            'T-Shirts',
            'Shirts',
            'Polos',
        ])->map(fn (string $category) => [
            'name' => $category,
            'slug' => Str::slug($category),
        ])->values();

        return response()->json(['data' => $categories]);
    }

    public function show(WearProduct $product): WearProductResource
    {
        abort_unless($product->is_active, 404);

        $product->load('variants');

        return new WearProductResource($product);
    }
}
