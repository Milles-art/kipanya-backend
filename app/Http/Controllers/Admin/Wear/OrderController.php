<?php

namespace App\Http\Controllers\Admin\Wear;

use App\Enums\Commerce\OrderStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Wear\WearOrder;
use App\Support\AuditLogger;
use App\Services\Commerce\OrderStateService;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function updateStatus(Request $request, WearOrder $order, OrderStateService $stateService, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('commerce.manage'), 403);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $target = OrderStatus::from($validated['status']);

        if ($target === $order->status) {
            return back()->with('success', 'Order status is already '.$target->value.'.');
        }

        $from = $order->status->value;

        $updated = $stateService->transition($order, $target, $request->user(), $validated['reason'] ?? null);

        $auditLogger->log($request, 'admin.wear.order.status_changed', $updated, [
            'from' => $from,
            'to' => $target->value,
            'reason' => $validated['reason'] ?? null,
        ]);

        return back()->with('success', 'Order status updated to '.$target->value.'.');
    }

    public function updateDelivery(Request $request, WearOrder $order): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('commerce.manage'), 403);

        $validated = $request->validate([
            'delivery_provider' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:120'],
            'fulfillment_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $order->update([
            'delivery_provider' => filled($validated['delivery_provider'] ?? null) ? trim($validated['delivery_provider']) : null,
            'tracking_number' => filled($validated['tracking_number'] ?? null) ? trim($validated['tracking_number']) : null,
            'fulfillment_notes' => filled($validated['fulfillment_notes'] ?? null) ? trim($validated['fulfillment_notes']) : null,
        ]);

        app(AuditLogger::class)->log($request, 'admin.wear.order.delivery_updated', $order, [
            'delivery_provider' => $order->delivery_provider,
            'tracking_number' => $order->tracking_number,
        ]);

        return back()->with('success', 'Delivery details updated.');
    }
}
