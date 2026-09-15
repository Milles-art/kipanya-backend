<?php

namespace App\Http\Controllers\Admin\Wear;

use App\Enums\Commerce\OrderStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Wear\WearOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class OrderController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('commerce.manage'), 403);

        $orders = WearOrder::query()
            ->withCount('items')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = trim((string) $request->string('q'));
                $query->where(function ($query) use ($term): void {
                    $query->where('order_number', 'like', "%{$term}%")
                        ->orWhere('customer_name', 'like', "%{$term}%")
                        ->orWhere('customer_phone', 'like', "%{$term}%")
                        ->orWhere('customer_email', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->string('payment_status')))
            ->latest('placed_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.wear.orders.index', [
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
        ]);
    }

    public function show(Request $request, WearOrder $order): View
    {
        abort_unless($request->user()?->hasPermission('commerce.manage'), 403);

        $order->load([
            'user:id,name,email,phone',
            'items.product:id,name,image_path',
            'items.variant:id,wear_product_id,sku,size,color,stock',
            'payments',
            'statusHistory.changedBy:id,name,email',
        ]);

        return view('admin.wear.orders.show', [
            'order' => $order,
            'statuses' => OrderStatus::cases(),
        ]);
    }

    public function updateStatus(Request $request, WearOrder $order): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('commerce.manage'), 403);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $target = OrderStatus::from($validated['status']);
        $current = $order->status;

        if ($target === $current) {
            return back()->with('success', 'Order status is already '.$target->value.'.');
        }

        if ($target === OrderStatus::PendingPayment) {
            return back()->withErrors(['status' => 'Orders cannot be moved back to pending payment from the admin panel.']);
        }

        if ($target === OrderStatus::Confirmed && $order->payment_status !== PaymentStatus::Paid) {
            return back()->withErrors(['status' => 'An order can only be confirmed after payment is marked as paid.']);
        }

        $allowed = match ($current) {
            OrderStatus::PendingPayment => [OrderStatus::Cancelled, OrderStatus::Confirmed],
            OrderStatus::Confirmed => [OrderStatus::Processing, OrderStatus::Cancelled],
            OrderStatus::Processing => [OrderStatus::Shipped, OrderStatus::Cancelled],
            OrderStatus::Shipped => [OrderStatus::Delivered],
            OrderStatus::Delivered => [],
            OrderStatus::Cancelled => [],
        };

        if (! in_array($target, $allowed, true)) {
            return back()->withErrors([
                'status' => sprintf('Cannot change an order from %s to %s.', $current->value, $target->value),
            ]);
        }

        DB::transaction(function () use ($order, $current, $target, $validated, $request): void {
            $order->update(['status' => $target]);

            $order->statusHistory()->create([
                'from_status' => $current->value,
                'to_status' => $target->value,
                'reason' => $validated['reason'] ?? null,
                'changed_by' => $request->user()->id,
            ]);
        });

        return back()->with('success', 'Order status updated to '.$target->value.'.');
    }
}
