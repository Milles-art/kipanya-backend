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
use Illuminate\Support\Facades\DB;
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

        $states = app(PaymentStateMachine::class);
        $attempt = DB::transaction(function () use ($order, $payments, $states) {
            $order = WearOrder::query()->lockForUpdate()->findOrFail($order->id);
            $states->assertPayable($order);
            $previousAttempts = $order->payments()->lockForUpdate()->orderByDesc('id')->get();
            $latest = $previousAttempts->first();

            $uncertain = fn ($p): bool => ($p->payload['initiation_uncertain'] ?? false) === true;
            foreach ($previousAttempts as $previous) {
                if (! in_array($previous->status, [PaymentStatus::Processing, PaymentStatus::InProgress, PaymentStatus::ReconciliationRequired], true)) {
                    continue;
                }
                // An attempt whose initiation failed ambiguously never handed the
                // customer a payment URL, so no money can be on it. Blocking on it
                // would make the order permanently unpayable after one gateway
                // hiccup; it is safe to supersede and retry.
                if ($uncertain($previous)) {
                    continue;
                }
                throw ValidationException::withMessages(['payment' => 'A payment is still being checked. Please wait before trying again.']);
            }
            if ($latest && ! $uncertain($latest) && $latest->status->isPendingLike()
                && (! $latest->initiated_at || $latest->initiated_at->gt(now()->subMinutes(2)))) {
                return $latest;
            }
            foreach ($previousAttempts as $previous) {
                $states->supersede($previous);
            }
            return $payments->prepare($order, 'repay-'.Str::uuid()->toString(), ['repay' => true]);
        });
        $payments->initiatePrepared($attempt);

        return new OrderResource($order->fresh(['items', 'payments', 'stockReservation.items.variant.product']));
    }
}
