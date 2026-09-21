<?php

namespace App\Http\Controllers\Admin\Wear;

use App\Enums\Commerce\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Commerce\PaymentTransaction;
use Illuminate\Http\Request;
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

    private function authorizeAdmin(Request $request): void
    {
        abort_unless(
            $request->user()?->isAdmin() && $request->user()->hasPermission('payments.manage'),
            403,
        );
    }
}
