<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\PaymentStatus;
use App\Models\Commerce\PaymentTransaction;
use App\Models\Wear\WearInventoryMovement;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearReturnRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class WearReturnService
{
    public function approve(WearReturnRequest $request, ?int $actorId = null): WearReturnRequest
    {
        return DB::transaction(function () use ($request, $actorId): WearReturnRequest {
            $return = WearReturnRequest::query()->lockForUpdate()->with('order.items')->findOrFail($request->id);
            if ($return->status !== 'under_review') {
                throw ValidationException::withMessages(['return' => 'Only requests under review can be approved.']);
            }
            $return->update(['status' => 'approved', 'approved_at' => now()]);
            return $return->fresh('order.items');
        });
    }

    public function reject(WearReturnRequest $request): WearReturnRequest
    {
        return DB::transaction(function () use ($request): WearReturnRequest {
            $return = WearReturnRequest::query()->lockForUpdate()->findOrFail($request->id);
            if ($return->status !== 'under_review') {
                throw ValidationException::withMessages(['return' => 'Only requests under review can be rejected.']);
            }
            $return->update(['status' => 'rejected']);
            return $return->fresh();
        });
    }

    public function receive(WearReturnRequest $request): WearReturnRequest
    {
        return DB::transaction(function () use ($request): WearReturnRequest {
            $return = WearReturnRequest::query()->lockForUpdate()->findOrFail($request->id);
            if ($return->status !== 'approved') {
                throw ValidationException::withMessages(['return' => 'Only approved returns can be marked received.']);
            }
            $return->update(['status' => 'received', 'received_at' => now()]);
            return $return->fresh();
        });
    }

    /**
     * Process a physically received return exactly once. Inventory is restored
     * atomically and a movement is recorded for every returned line.
     */
    public function process(WearReturnRequest $request, ?int $actorId = null): WearReturnRequest
    {
        return DB::transaction(function () use ($request, $actorId): WearReturnRequest {
            $return = WearReturnRequest::query()->lockForUpdate()->with('order.items')->findOrFail($request->id);
            if ($return->status !== 'received') {
                throw ValidationException::withMessages(['return' => 'Only received returns can be processed.']);
            }
            if ($return->request_type === 'exchange') {
                throw ValidationException::withMessages(['return' => 'Exchange fulfillment is not enabled yet. Process this request through a dedicated exchange workflow.']);
            }

            $order = WearOrder::query()->lockForUpdate()->findOrFail($return->wear_order_id);
            $itemIds = collect($return->order_item_ids ?? [])->map(fn ($id) => (int) $id)->unique();
            $items = $order->items()->whereIn('id', $itemIds)->lockForUpdate()->get();
            if ($items->count() !== $itemIds->count()) {
                throw ValidationException::withMessages(['return' => 'The return contains an invalid order item.']);
            }

            $refund = 0.0;
            foreach ($items as $item) {
                $variant = $item->variant()->lockForUpdate()->first();
                if (! $variant) {
                    throw ValidationException::withMessages(['return' => "Variant for {$item->product_name} no longer exists."]);
                }

                $before = (int) $variant->stock;
                $quantity = (int) $item->quantity;
                $variant->increment('stock', $quantity);
                $after = $before + $quantity;

                WearInventoryMovement::query()->create([
                    'wear_product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'reason' => 'Return restock',
                    'notes' => "Return #{$return->id} / {$order->order_number}",
                    'created_by' => $actorId,
                ]);

                $refund += (float) $item->line_total;
            }

            $return->update([
                'status' => 'processed',
                'refund_amount' => number_format($refund, 2, '.', ''),
                'refund_status' => $order->payment_status === PaymentStatus::Paid ? 'pending' : 'not_required',
                'processed_at' => now(),
            ]);

            return $return->fresh('order.items');
        });
    }

    /**
     * Records a completed manual/provider refund. Actual gateway calls remain
     * outside this service until a real payment adapter is connected.
     */
    public function markRefunded(WearReturnRequest $request, string $reference, ?int $actorId = null): WearReturnRequest
    {
        return DB::transaction(function () use ($request, $reference, $actorId): WearReturnRequest {
            $return = WearReturnRequest::query()->lockForUpdate()->findOrFail($request->id);
            if ($return->status !== 'processed') {
                throw ValidationException::withMessages(['return' => 'Only processed returns can be refunded.']);
            }
            if ($return->refund_status !== 'pending') {
                throw ValidationException::withMessages(['return' => 'This return does not have a pending refund.']);
            }

            $order = WearOrder::query()->lockForUpdate()->findOrFail($return->wear_order_id);
            $payment = PaymentTransaction::query()
                ->where('wear_order_id', $order->id)
                ->whereIn('status', [PaymentStatus::Paid->value, PaymentStatus::Refunded->value])
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                throw ValidationException::withMessages(['return' => 'No completed payment is available for this refund.']);
            }

            $amount = (float) $return->refund_amount;
            $alreadyRefunded = (float) $payment->refunded_amount;
            $remaining = max(0, (float) $payment->amount - $alreadyRefunded);
            if ($amount <= 0 || $amount > $remaining) {
                throw ValidationException::withMessages(['return' => 'Refund amount exceeds the remaining refundable payment amount.']);
            }

            $newRefunded = $alreadyRefunded + $amount;
            $payment->update([
                'refunded_amount' => number_format($newRefunded, 2, '.', ''),
                'status' => $newRefunded >= (float) $payment->amount ? PaymentStatus::Refunded : PaymentStatus::Paid,
                'payload' => array_merge($payment->payload ?? [], [
                    'refunds' => array_merge($payment->payload['refunds'] ?? [], [[
                        'return_id' => $return->id,
                        'amount' => $amount,
                        'reference' => trim($reference),
                        'completed_at' => now()->toISOString(),
                        'completed_by' => $actorId,
                    ]]),
                ]),
            ]);

            if ($newRefunded >= (float) $payment->amount) {
                $order->update(['payment_status' => PaymentStatus::Refunded]);
            }

            $return->update([
                'status' => 'refunded',
                'refund_status' => 'completed',
                'refund_reference' => trim($reference),
                'refunded_at' => now(),
            ]);

            return $return->fresh('order.items');
        });
    }

    public function complete(WearReturnRequest $request): WearReturnRequest
    {
        return DB::transaction(function () use ($request): WearReturnRequest {
            $return = WearReturnRequest::query()->lockForUpdate()->findOrFail($request->id);
            if ($return->status !== 'refunded' && $return->refund_status !== 'not_required') {
                throw ValidationException::withMessages(['return' => 'A return can only be completed after refund processing.']);
            }
            $return->update(['status' => 'completed', 'completed_at' => now()]);
            return $return->fresh();
        });
    }
}
