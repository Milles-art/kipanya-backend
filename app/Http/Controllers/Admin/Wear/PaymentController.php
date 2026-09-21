<?php

namespace App\Http\Controllers\Admin\Wear;

use App\Enums\Commerce\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Commerce\PaymentTransaction;
use App\Models\Wear\WearReturnRequest;
use App\Services\Payments\PaymentService;
use App\Services\Payments\PaymentStateMachine;
use App\Support\AdminStepUp;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');

        $payments = PaymentTransaction::query()
            ->with(['order:id,order_number,customer_name,customer_phone,total', 'user:id,name,phone'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('provider_reference', 'like', "%{$search}%")
                        ->orWhere('provider', 'like', "%{$search}%")
                        ->orWhereHas('order', function ($order) use ($search) {
                            $order->where('order_number', 'like', "%{$search}%")
                                ->orWhere('customer_name', 'like', "%{$search}%")
                                ->orWhere('customer_phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($status, array_map(fn (PaymentStatus $s) => $s->value, PaymentStatus::cases()), true),
                fn ($query) => $query->where('status', $status))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.wear.payments.index', [
            'payments' => $payments,
            'search' => $search,
            'status' => $status,
            'statuses' => PaymentStatus::cases(),
            'attentionCount' => PaymentTransaction::query()->where('status', PaymentStatus::ReconciliationRequired->value)->count(),
        ]);
    }

    public function show(Request $request, PaymentTransaction $payment): View
    {
        $this->authorizeAdmin($request);

        $payment->load([
            'order.items',
            'order.statusHistory' => fn ($query) => $query->latest(),
            'user',
        ]);

        return view('admin.wear.payments.show', compact('payment'));
    }

    /** Ask the provider for the payment's current state (settles or flags it). */
    public function recheck(Request $request, PaymentTransaction $payment, PaymentService $payments): RedirectResponse
    {
        $this->authorizeAdmin($request);

        abort_unless(in_array($payment->status, [
            PaymentStatus::Pending, PaymentStatus::Processing, PaymentStatus::InProgress, PaymentStatus::ReconciliationRequired,
        ], true), 422);

        try {
            $payments->refreshStatus($payment);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['payment' => 'Could not reach the payment provider. Please try again shortly.']);
        }

        app(AuditLogger::class)->log($request, 'payment.rechecked', $payment, ['status_after' => $payment->fresh()->status->value]);

        return back()->with('status', 'Provider status refreshed.');
    }

    /** Record that a flagged payment was refunded to the customer outside this system. */
    public function refund(Request $request, PaymentTransaction $payment, PaymentStateMachine $machine, AdminStepUp $stepUp): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $stepUp->assert($request);

        $data = $request->validate(['refund_reference' => ['required', 'string', 'min:4', 'max:100']]);
        $reference = trim($data['refund_reference']);

        $reused = WearReturnRequest::query()->where('refund_reference', $reference)->exists()
            || PaymentTransaction::query()->where('status', PaymentStatus::Refunded->value)->get()
                ->contains(fn (PaymentTransaction $p) => ($p->payload['refund_reference'] ?? null) === $reference);

        if ($reused) {
            throw ValidationException::withMessages(['refund_reference' => 'This refund reference has already been used.']);
        }

        $machine->recordRefund($payment, $reference, $request->user()->id);

        return back()->with('status', 'Refund recorded.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless(
            $request->user()?->isAdmin() && $request->user()->hasPermission('payments.manage'),
            403,
        );
    }
}
