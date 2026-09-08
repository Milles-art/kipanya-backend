<?php

namespace App\Console\Commands;

use App\Enums\Commerce\OrderStatus;
use App\Enums\Commerce\ReservationStatus;
use App\Models\Commerce\StockReservation;
use App\Services\Commerce\InventoryReservationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireStockReservations extends Command
{
    protected $signature = 'kipanya:expire-stock-reservations';
    protected $description = 'Expire unpaid Wear stock reservations and cancel their pending orders.';

    public function handle(InventoryReservationService $inventory): int
    {
        StockReservation::query()
            ->where('status', ReservationStatus::Active->value)
            ->where('expires_at', '<=', now())
            ->with('order')
            ->chunkById(100, function ($reservations) use ($inventory): void {
                foreach ($reservations as $reservation) {
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
                            $order->update(['status' => OrderStatus::Cancelled]);
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
