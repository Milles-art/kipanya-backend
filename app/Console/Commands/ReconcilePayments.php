<?php

namespace App\Console\Commands;

use App\Enums\Commerce\PaymentStatus;
use App\Models\Commerce\PaymentTransaction;
use App\Services\Payments\PaymentService;
use Illuminate\Console\Command;

/**
 * Webhooks can be missed. Ask the provider about payments that are still unresolved so
 * a paid customer is fulfilled inside the reservation window, and money captured after
 * cancellation/expiry is flagged for refund instead of going unnoticed.
 */
class ReconcilePayments extends Command
{
    protected $signature = 'kipanya:reconcile-payments {--minutes=2 : Only payments unresolved for at least this many minutes}';

    protected $description = 'Poll the payment provider for unresolved payments and settle or flag them.';

    public function handle(PaymentService $payments): int
    {
        $checked = 0;
        $failed = 0;

        $poll = function (PaymentTransaction $payment) use ($payments, &$checked, &$failed): void {
            try {
                $payments->refreshStatus($payment);
                $checked++;
            } catch (\Throwable $e) {
                // One bad payment (provider hiccup, validation) must not stop the rest.
                $failed++;
                report($e);
            }
        };

        PaymentTransaction::query()
            ->whereIn('status', [PaymentStatus::Pending->value, PaymentStatus::Processing->value, PaymentStatus::InProgress->value])
            ->where('created_at', '<=', now()->subMinutes(max(0, (int) $this->option('minutes'))))
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('id')
            ->chunkById(50, fn ($chunk) => $chunk->each($poll));

        // Held for review (e.g. completed without an amount): give the provider another look.
        PaymentTransaction::query()
            ->where('status', PaymentStatus::ReconciliationRequired->value)
            ->where('updated_at', '<=', now()->subMinutes(5))
            ->orderBy('id')
            ->chunkById(50, fn ($chunk) => $chunk->filter(fn ($p) => ($p->payload['needs_review'] ?? false) === true)->each($poll));

        $this->info("Reconciled {$checked} payment(s); {$failed} could not be checked.");

        return self::SUCCESS;
    }
}
