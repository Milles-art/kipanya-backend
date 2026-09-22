<?php

namespace App\Http\Controllers\Api\V1\Commerce;

use App\DTOs\Commerce\CreateWearOrderData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Commerce\CreateOrderRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Services\Cart\CartService;
use App\Services\Checkout\CheckoutPreviewService;
use App\Services\Commerce\OrderService;
use Illuminate\Http\Request;

final class CheckoutController extends Controller
{
    public function store(CreateOrderRequest $request, OrderService $orders): OrderResource
    {
        $idempotencyKey = $request->header('Idempotency-Key');

        if (! is_string($idempotencyKey) || ! preg_match('/^[A-Za-z0-9._-]{16,100}$/', $idempotencyKey)) {
            abort(400, 'A valid Idempotency-Key header is required.');
        }

        $order = $orders->create(
            $request->user(),
            new CreateWearOrderData(
                addressId: $request->integer('address_id'),
                notes: $request->input('notes'),
                idempotencyKey: $idempotencyKey,
                paymentMethod: $request->input('payment_method'),
            ),
        );

        return new OrderResource($order->load(['items', 'payments', 'stockReservation.items.variant.product']));
    }

    public function preview(Request $request, CheckoutPreviewService $preview): mixed
    {
        $cart = app(CartService::class)->current(
            $request->user(),
            $request->header(CartService::HEADER),
        );

        return response()->json(['data' => $preview->preview($cart)]);
    }
}
