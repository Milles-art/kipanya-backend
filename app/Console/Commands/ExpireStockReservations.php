<?php

namespace App\Console\Commands;

use App\Enums\Commerce\OrderStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Enums\Commerce\ReservationStatus;
use App\Models\Commerce\StockReservation;
use App\Services\Commerce\InventoryReservationService;
use App\Services\Payments\PaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireStockReservations extends Command
{
    protected $signature = 'kipanya:expire-stock-reservations';
    protected $description = 'Expire unpaid Wear stock reservations and cancel their pending orders.';

    public function handle(InventoryReservationService $inventory, PaymentService $payments): int
    {
        StockReservation::query()
            ->where('status', ReservationStatus::Active->value)
            ->where('expires_at', '<=', now())
            ->with('order')
            ->chunkById(100, function ($reservations) use ($inventory, $payments): void {
                foreach ($reservations as $reservation) {
                    // Before giving the stock back, ask the provider about any payment that is
                    // still unresolved: a customer who paid at the last minute (webhook late or
                    // missed) must be fulfilled, not cancelled. Done outside the transaction so
                    // no row locks are held during the provider call.
                    $reservation->order?->payments()
                        ->whereIn('status', [PaymentStatus::Pending->value, PaymentStatus::Processing->value, PaymentStatus::InProgress->value])
                        ->get()
                        ->each(function ($payment) use ($payments): void {
                            try {
                                $payments->refreshStatus($payment);
                            } catch (\Throwable $e) {
                                report($e);
                            }
                        });

                    DB::transaction(function () use ($reservation, $inventory): void {
                        $locked = StockReservation::query()->lockForUpdate()->with('order')->find($reservation->id);
                        if (! $locked || $locked->status !== ReservationStatus::Active || $locked->expires_at?->isFuture()) {
                            return;
                        }

                        $order = $locked->order()->lockForUpdate()->first();
                        $locked->update([
                            'status' => ReservationStatus::Expired,
                            'released_at' => now(),
                        ]);

                        if ($order && $order->status === OrderStatus::PendingPayment) {
                            $order->update([
                                'status' => OrderStatus::Cancelled,
                                'payment_status' => PaymentStatus::Cancelled,
                            ]);
                            $order->payments()
                                ->whereIn('status', [PaymentStatus::Pending->value, PaymentStatus::Processing->value, PaymentStatus::InProgress->value])
                                ->update(['status' => PaymentStatus::Cancelled]);
                            $order->statusHistory()->create([
                                'from_status' => OrderStatus::PendingPayment->value,
                                'to_status' => OrderStatus::Cancelled->value,
                                'reason' => 'Payment reservation expired.',
                            ]);
                        }
                    });
                }
            });

        return self::SUCCESS;
    }
}
