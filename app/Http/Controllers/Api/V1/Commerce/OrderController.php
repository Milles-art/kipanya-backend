<?php

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Enums\Commerce\OrderStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Http\Controllers\Api\V1\Concerns\AuthorizesUserOwnership;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Services\Commerce\OrderService;
use App\Services\Payments\PaymentService;
use App\Services\Payments\PaymentStateMachine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

    /**
     * Mint a fresh payment token for an unpaid order ("Try again").
     *
     * Selcom payment links expire, so retrying with the original URL can
     * strand the customer on a dead page. Repay supersedes stale
     * pending-like attempts and initiates a new one through the same
     * gateway + state machine as checkout, returning the order with the
     * fresh payment_gateway_url the page redirects to.
     */
    public function repay(Request $request, WearOrder $order, PaymentService $payments): OrderResource
    {
        $this->assertOwnedByOrCan($order, 'commerce.manage');

        if ($order->status !== OrderStatus::PendingPayment) {
            throw ValidationException::withMessages([
                'payment' => 'This order can no longer be paid.',
            ]);
        }

        $order->loadMissing(['payments']);
        $latest = $order->payments->sortByDesc('id')->first();

        if ($latest !== null && $latest->status === PaymentStatus::Paid) {
            throw ValidationException::withMessages([
                'payment' => 'This order is already paid.',
            ]);
        }

        // A token minted moments ago is still fresh — reuse it so double
        // taps and rapid retries don't stack up pending payments.
        if ($latest !== null
            && $latest->status->isPendingLike()
            && $latest->initiated_at !== null
            && $latest->initiated_at->gt(now()->subMinutes(2))) {
            return new OrderResource($order->load(['items', 'payments', 'stockReservation.items.variant.product']));
        }

        // Supersede stale pending-like attempts so only the new token can settle.
        $states = app(PaymentStateMachine::class);
        foreach ($order->payments as $previous) {
            if ($previous->status->isPendingLike() && $previous->status->canTransitionTo(PaymentStatus::Cancelled)) {
                $states->transition($previous, PaymentStatus::Cancelled, [
                    'reason' => 'superseded by repay',
                ]);
            }
        }

        $payments->createPending($order, 'repay-'.Str::uuid()->toString(), [
            'repay' => true,
        ]);

        return new OrderResource($order->fresh(['items', 'payments', 'stockReservation.items.variant.product']));
    }
}
