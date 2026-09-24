<?php

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Http\Controllers\Api\V1\Concerns\AuthorizesUserOwnership;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Services\Commerce\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class OrderController extends Controller
{
    use AuthorizesUserOwnership;
    public function __construct(private readonly OrderService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();
        return OrderResource::collection(
            WearOrder::query()->where('user_id', $user->id)->latest('id')->paginate(20)->through(
                fn (WearOrder $order) => $order->load(['items', 'payments', 'stockReservation'])
            )
        );
    }

    public function show(Request $request, WearOrder $order): OrderResource
    {
        $this->assertOwnedByOrCan($order, 'commerce.manage');
        return new OrderResource($order->load(['items', 'payments', 'statusHistory', 'stockReservation.items.variant.product']));
    }

    public function cancel(Request $request, WearOrder $order): OrderResource
    {
        return new OrderResource($this->service->cancel($order, $request->user()));
    }
}
