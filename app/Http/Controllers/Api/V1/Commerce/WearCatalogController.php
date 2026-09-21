<?php

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Commerce\WearProductIndexRequest;
use App\Http\Resources\Api\V1\Commerce\WearCollectionResource;
use App\Http\Resources\Api\V1\Commerce\WearProductResource;
use App\Models\Wear\WearCollection;
use App\Models\Wear\WearProduct;
use Illuminate\Database\Eloquent\Builder;
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
                function (Builder $query) use ($request): void {
                    $requested = $request->string('category')->trim()->toString();
                    $query->where(function (Builder $categoryQuery) use ($requested): void {
                        $categoryQuery
                            ->where('category', $requested)
                            ->orWhereRaw("LOWER(REPLACE(category, ' ', '-')) = ?", [Str::lower($requested)]);
                    });
                }
            )
            ->when($request->boolean('featured'), fn (Builder $query) => $query->where('is_featured', true))
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $term = '%'.Str::lower($request->string('q')->trim()->toString()).'%';
                $query->where(function (Builder $search) use ($term): void {
                    $search->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(description) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(category) LIKE ?', [$term]);
                });
            })
            ->when($request->filled('price_min'), fn (Builder $query) => $query->where('price', '>=', $request->input('price_min')))
            ->when($request->filled('price_max'), fn (Builder $query) => $query->where('price', '<=', $request->input('price_max')))
            ->when($request->boolean('sale'), fn (Builder $query) => $query->whereNotNull('compare_at_price')->whereColumn('compare_at_price', '>', 'price'));

        match ($request->input('sort', 'featured')) {
            'price-asc' => $products->orderBy('price')->orderBy('id'),
            'price-desc' => $products->orderByDesc('price')->orderBy('id'),
            'newest' => $products->orderByDesc('id'),
            default => $products->orderByDesc('is_featured')->orderBy('sort_order')->orderBy('id'),
        };

        $products = $products->paginate($request->integer('per_page', 20));

        return WearProductResource::collection($products);
    }

    public function collections(): AnonymousResourceCollection
    {
        $collections = WearCollection::query()
            ->where('is_active', true)
            ->withCount(['products' => fn (Builder $query) => $query->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(200)
            ->get();

        return WearCollectionResource::collection($collections);
    }

    public function collection(WearCollection $collection): WearCollectionResource
    {
        abort_unless($collection->is_active, 404);

        // Bound public collection payloads so a large collection cannot cause
        // unbounded product/variant hydration on a single anonymous request.
        $collection->load([
            'products' => fn ($query) => $query
                ->where('is_active', true)
                ->with('variants')
                ->orderBy('wear_products.sort_order')
                ->orderBy('wear_products.id')
                ->limit(60),
        ]);

        return new WearCollectionResource($collection);
    }

    public function categories(): JsonResponse
    {
        $categoryNames = ['Hoodies', 'Long Sleeves', 'T-Shirts', 'Shirts', 'Polos'];

        // Resolve one representative product per category with a LIMIT 1 query
        // instead of hydrating the whole catalog just to pick the first row.
        $categories = collect($categoryNames)->map(function (string $name): array {
            $product = WearProduct::query()
                ->where('is_active', true)
                ->where('category', $name)
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->first(['category', 'image_path']);

            return [
                'name' => $name,
                'slug' => Str::slug($name),
                'image' => $product?->image_url,
            ];
        })->values();

        return response()->json(['data' => $categories]);
    }

    public function show(WearProduct $product): WearProductResource
    {
        abort_unless($product->is_active, 404);

        $product->load(['variants', 'images']);

        return new WearProductResource($product);
    }
}
