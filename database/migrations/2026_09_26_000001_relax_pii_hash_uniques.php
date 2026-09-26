<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Relax blind-index uniqueness on non-identity PII.
 *
 * An earlier revision of the remaining-PII migration created UNIQUE indexes
 * on `addresses.phone_hash`, `addresses.recipient_name_hash`,
 * `otp_codes.phone_hash` and `contact_messages.email_hash`. That is wrong:
 * many addresses share a phone or recipient name, OTPs are re-issued per
 * phone, and senders message repeatedly — all legitimate duplicates that the
 * unique constraints reject. Only `users.phone_hash` / `users.email_hash`
 * stay unique, because they are the login identity.
 *
 * This migration drops those unique indexes (if present) and replaces them
 * with plain lookup indexes. SQLite builds are unaffected: fresh SQLite
 * databases already get plain indexes from the corrected migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        $this->relax('addresses', ['phone_hash', 'recipient_name_hash']);
        $this->relax('otp_codes', ['phone_hash']);
        $this->relax('contact_messages', ['email_hash']);
    }

    public function down(): void
    {
        // Intentionally a no-op: re-imposing uniqueness could fail on
        // legitimate duplicate hashes accumulated while relaxed.
    }

    /**
     * Drop every unique index covering the given columns, then ensure a
     * plain lookup index exists on each of them.
     */
    private function relax(string $table, array $columns): void
    {
        $existing = collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->groupBy('Key_name');

        foreach ($existing as $name => $entries) {
            $indexed = collect($entries)->pluck('Column_name')->all();
            $isUnique = ((int) $entries[0]->Non_unique) === 0;

            if (! $isUnique || $name === 'PRIMARY') {
                continue;
            }

            if (count(array_intersect($indexed, $columns)) > 0) {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
            }
        }

        foreach ($columns as $column) {
            $index = "{$table}_{$column}_index";

            $names = collect(DB::select("SHOW INDEX FROM `{$table}`"))
                ->pluck('Key_name');

            if (! $names->contains($index)) {
                DB::statement("CREATE INDEX `{$index}` ON `{$table}` (`{$column}`)");
            }
        }
    }
};
