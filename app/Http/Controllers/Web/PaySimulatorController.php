<?php

namespace App\Http\Controllers\Web;

use App\Enums\Commerce\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Commerce\PaymentTransaction;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Services\Payments\PaymentStateMachine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Local payment simulator for the fake gateway.
 *
 * Selcom's hosted page cannot run outside production, so locally the fake
 * gateway points here instead. The page shows the pending payment and lets a
 * developer complete or fail it, exercising the real state machine
 * (fulfilment, inventory, order status) end to end.
 *
 * Hard-disabled in production: every action aborts with 404 there, and the
 * fake gateway itself refuses to boot in production, so no simulator URL can
 * ever be issued outside local development and testing.
 */
final class PaySimulatorController extends Controller
{
    public function show(Request $request, string $orderNumber): View
    {
        abort_unless(! app()->isProduction(), 404);

        $user = $this->user($request);
        $order = $this->ownedOrder($user, $orderNumber);
        $payment = $this->payablePayment($order);

        return view('pages.pay-simulate', [
            'title' => 'Simulated payment — KP Wear',
            'order' => $order,
            'payment' => $payment,
        ]);
    }

    public function complete(Request $request, string $orderNumber, PaymentStateMachine $state): RedirectResponse
    {
        abort_unless(! app()->isProduction(), 404);

        $user = $this->user($request);
        $order = $this->ownedOrder($user, $orderNumber);
        $payment = $this->payablePayment($order);

        abort_unless($payment !== null, 422, 'There is no payable payment on this order.');

        $state->transition($payment, PaymentStatus::Paid, [
            'transid' => 'SIM-'.Str::upper(Str::random(12)),
            'simulated' => true,
            'amount' => (float) $order->total,
        ]);

        return redirect()->route('order-status', $order->order_number);
    }

    public function fail(Request $request, string $orderNumber, PaymentStateMachine $state): RedirectResponse
    {
        abort_unless(! app()->isProduction(), 404);

        $user = $this->user($request);
        $order = $this->ownedOrder($user, $orderNumber);
        $payment = $this->payablePayment($order);

        abort_unless($payment !== null, 422, 'There is no payable payment on this order.');

        $state->transition($payment, PaymentStatus::Failed, [
            'simulated' => true,
            'reason' => 'Simulated provider failure.',
        ]);

        return redirect()->route('order-status', $order->order_number);
    }

    /**
     * Scoped lookup: another customer's order (or a missing one) is 404, so
     * order numbers cannot be probed and payments cannot be touched.
     */
    private function ownedOrder(User $user, string $orderNumber): WearOrder
    {
        return WearOrder::query()
            ->where('user_id', $user->id)
            ->where('order_number', $orderNumber)
            ->with(['items', 'payments'])
            ->firstOrFail();
    }

    private function payablePayment(WearOrder $order): ?PaymentTransaction
    {
        return $order->payments
            ->sortByDesc('id')
            ->first(fn (PaymentTransaction $payment): bool => $payment->status->isPendingLike());
    }

    private function user(Request $request): User
    {
        return $request->user() ?? auth('sanctum')->user();
    }
}
