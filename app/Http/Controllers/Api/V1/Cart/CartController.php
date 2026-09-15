<?php

namespace App\Http\Controllers\Api\V1\Cart;

use App\Actions\Cart\AddCartItem;
use App\Actions\Cart\MergeGuestCart;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Cart\AddCartItemRequest;
use App\Http\Requests\Api\V1\Cart\UpdateCartItemRequest;
use App\Http\Resources\Api\V1\CartResource;
use App\Models\Wear\WearProductVariant;
use App\Services\Cart\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $service,
        private readonly AddCartItem $add,
        private readonly MergeGuestCart $merge,
    ) {}

    public function show(Request $request): CartResource
    {
        $this->validateGuestToken($request);
        return $this->resource(
            $this->service->current($this->authenticatedUser($request), $request->header(CartService::HEADER))
                ->load('items.variant.product'),
            $request,
        );
    }

    public function store(AddCartItemRequest $request): CartResource
    {
        $this->validateGuestToken($request);
        $cart = $this->service->current($this->authenticatedUser($request), $request->header(CartService::HEADER));
        $variant = WearProductVariant::with('product')->findOrFail($request->integer('variant_id'));
        return $this->resource($this->add->execute($cart, $variant, $request->integer('quantity')), $request);
    }

    public function update(UpdateCartItemRequest $request, int $variant): CartResource
    {
        $this->validateGuestToken($request);
        $cart = $this->service->current($this->authenticatedUser($request), $request->header(CartService::HEADER));
        $model = WearProductVariant::with('product')->findOrFail($variant);
        return $this->resource($this->service->set($cart, $model, $request->integer('quantity')), $request);
    }

    public function destroy(Request $request, int $variant): CartResource
    {
        $this->validateGuestToken($request);
        $cart = $this->service->current($this->authenticatedUser($request), $request->header(CartService::HEADER));
        $model = WearProductVariant::with('product')->findOrFail($variant);
        return $this->resource($this->service->remove($cart, $model), $request);
    }

    public function clear(Request $request): CartResource
    {
        $this->validateGuestToken($request);
        $cart = $this->service->current($this->authenticatedUser($request), $request->header(CartService::HEADER));
        return $this->resource($this->service->clear($cart), $request);
    }

    public function merge(Request $request): CartResource
    {
        $data = $request->validate([
            'guest_cart_token' => ['required', 'string', 'regex:/^[a-f0-9]{64}(?:[a-f0-9]{32})?$/'],
        ]);

        return $this->resource(
            $this->merge->execute($data['guest_cart_token'], $request->user()),
            $request,
        );
    }

    private function validateGuestToken(Request $request): void
    {
        if ($this->authenticatedUser($request)) {
            return;
        }

        $token = $request->header(CartService::HEADER);
        if ($token !== null && ! preg_match('/^[a-f0-9]{64}(?:[a-f0-9]{32})?$/', $token)) {
            abort(422, 'Invalid guest cart token.');
        }
    }

    private function authenticatedUser(Request $request)
    {
        return $request->user() ?? Auth::guard('sanctum')->user();
    }

    private function resource($cart, Request $request): CartResource
    {
        if (! $cart->user_id) {
            $token = $request->header(CartService::HEADER);
            if ($token) {
                $cart->setAttribute('_guest_cart_token', $token);
            }
        }

        return new CartResource($cart);
    }
}
