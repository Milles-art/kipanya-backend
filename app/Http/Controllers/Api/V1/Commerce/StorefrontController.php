<?php

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Commerce\WearCollectionResource;
use App\Http\Resources\Api\V1\Commerce\WearProductResource;
use App\Models\Administration\StorefrontSetting;
use App\Models\Wear\WearCollection;
use App\Models\Wear\WearProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

final class StorefrontController extends Controller
{
    public function show(): JsonResponse
    {
        $productIds = StorefrontSetting::json('homepage_featured_product_ids');
        $collectionIds = StorefrontSetting::json('homepage_featured_collection_ids');

        $products = WearProduct::query()
            ->where('is_active', true)
            ->when($productIds, fn ($query) => $query->whereIn('id', $productIds))
            ->with('variants')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // Admin selections are optional presentation controls. If they are empty
        // or no longer resolve to active products, keep the public homepage alive
        // from the active Wear catalog instead of returning an empty section.
        if ($products->isEmpty()) {
            $products = WearProduct::query()
                ->where('is_active', true)
                ->with('variants')
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        } elseif ($productIds) {
            $productOrder = array_flip(array_map('intval', $productIds));
            $products = $products
                ->sortBy(fn (WearProduct $product) => $productOrder[(int) $product->id] ?? PHP_INT_MAX)
                ->values();
        }

        $collections = WearCollection::query()
            ->where('is_active', true)
            ->when($collectionIds, fn ($query) => $query->whereIn('id', $collectionIds))
            ->with(['products' => fn ($query) => $query->where('is_active', true)->with('variants')])
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($collections->isEmpty()) {
            $fallbackCategories = ['Hoodies', 'Long Sleeves', 'T-Shirts', 'Shirts', 'Polos'];
            $fallbackProducts = WearProduct::query()
                ->where('is_active', true)
                ->whereIn('category', $fallbackCategories)
                ->with('variants')
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->groupBy('category');

            $collections = collect($fallbackCategories)
                ->map(function (string $category, int $index) use ($fallbackProducts): WearCollection {
                    $collection = new WearCollection([
                        'name' => $category,
                        'slug' => Str::slug($category),
                        'description' => null,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ]);
                    $collection->exists = false;
                    $collection->setRelation('products', $fallbackProducts->get($category, collect())->values());

                    return $collection;
                })
                ->filter(fn (WearCollection $collection) => $collection->products->isNotEmpty())
                ->values();
        } elseif ($collectionIds) {
            $collectionOrder = array_flip(array_map('intval', $collectionIds));
            $collections = $collections
                ->sortBy(fn (WearCollection $collection) => $collectionOrder[(int) $collection->id] ?? PHP_INT_MAX)
                ->values();
        }

        return response()->json([
            'data' => [
                'hero' => [
                    'eyebrow' => StorefrontSetting::value('hero_eyebrow', 'KIPANYA WEAR'),
                    'title' => StorefrontSetting::value('hero_title', 'Wear your story.'),
                    'description' => StorefrontSetting::value('hero_description'),
                    'cta_label' => StorefrontSetting::value('hero_cta_label', 'Shop now'),
                    'cta_url' => StorefrontSetting::value('hero_cta_url', '/shop'),
                    'image_desktop' => StorefrontSetting::value('hero_image_desktop'),
                    'image_mobile' => StorefrontSetting::value('hero_image_mobile'),
                    'is_active' => StorefrontSetting::value('hero_is_active', '1') === '1',
                ],
                'homepage' => [
                    'featured_products' => WearProductResource::collection($products)->resolve(),
                    'featured_collections' => WearCollectionResource::collection($collections)->resolve(),
                ],
                'shop' => [
                    'banner' => [
                        'title' => StorefrontSetting::value('shop_banner_title'),
                        'description' => StorefrontSetting::value('shop_banner_description'),
                        'image' => StorefrontSetting::value('shop_banner_image'),
                        'is_active' => StorefrontSetting::value('shop_banner_is_active', '0') === '1',
                    ],
                    'default_sort' => StorefrontSetting::value('shop_default_sort', 'featured'),
                ],
            ],
        ]);
    }
}
