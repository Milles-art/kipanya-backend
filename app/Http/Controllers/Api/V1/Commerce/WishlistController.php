<?php

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Commerce\WearProductResource;
use App\Models\Cart\WishlistItem;
use App\Models\Wear\WearProduct;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $items = $request->user()
            ->wearWishlist()
            ->with('product')
            ->latest()
            ->paginate(24);

        return response()->json([
            'data' => collect($items->items())->map(fn (WishlistItem $item) => [
                'id' => $item->id,
                'product' => (new WearProductResource($item->product))->resolve($request),
            ])->values(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
            'links' => [
                'next' => $items->nextPageUrl(),
                'prev' => $items->previousPageUrl(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:wear_products,id'],
        ]);

        $product = WearProduct::findOrFail($data['product_id']);
        abort_unless($product->is_active, 404);

        $item = WishlistItem::firstOrCreate([
            'user_id' => $request->user()->id,
            'wear_product_id' => $product->id,
        ]);

        return response()->json([
            'data' => [
                'id' => $item->id,
                'product' => (new WearProductResource($product))->resolve($request),
            ],
        ], 201);
    }

    public function destroy(Request $request, WearProduct $product)
    {
        $request->user()
            ->wearWishlist()
            ->where('wear_product_id', $product->id)
            ->delete();

        return response()->noContent();
    }
}
