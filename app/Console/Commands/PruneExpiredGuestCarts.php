<?php

namespace App\Console\Commands;

use App\Models\Cart\Cart;
use Illuminate\Console\Command;

class PruneExpiredGuestCarts extends Command
{
    protected $signature = 'kipanya:prune-expired-guest-carts';

    protected $description = 'Delete expired guest carts (and their items) that were never merged into an account.';

    public function handle(): int
    {
        $deleted = Cart::query()
            ->whereNull('user_id')
            ->where(function ($query): void {
                $query->where('expires_at', '<=', now())
                    ->orWhere('updated_at', '<=', now()->subDays(30));
            })
            ->delete();

        $this->info("Pruned {$deleted} expired guest cart(s).");

        return self::SUCCESS;
    }
}
