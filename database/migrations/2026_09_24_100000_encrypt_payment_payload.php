<?php

use App\Models\Commerce\PaymentTransaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Encrypt the payment gateway payload at rest.
 *
 * `payment_transactions.payload` holds the provider's raw callback/initiate
 * response, including gateway references and customer-entered mobile money
 * numbers. It was stored as plaintext JSON, so any database dump, replica or
 * backup exposed it. Nothing queries INTO this column, so it can be encrypted
 * transparently without a blind-index side table.
 *
 * The column is widened from JSON to LONGTEXT because Laravel's encrypted
 * cast produces ciphertext larger than the original JSON and the ciphertext
 * is not valid JSON (so the JSON column's validation would reject it).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // SQLite stores TEXT as TEXT; JSON columns are TEXT with a CHECK
            // constraint. Rebuild the table to drop that constraint.
            $this->migrateSqlite();
        } else {
            Schema::table('payment_transactions', function (Blueprint $table): void {
                $table->longText('payload')->nullable()->change();
            });
        }

        $this->reencryptExistingRows();
    }

    public function down(): void
    {
        $this->decryptAllRowsToPlaintext();

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $this->revertSqlite();
        } else {
            Schema::table('payment_transactions', function (Blueprint $table): void {
                $table->json('payload')->nullable()->change();
            });
        }
    }

    /**
     * Re-encrypt rows that were written before the cast changed. Reading goes
     * through the model (which now decrypts), and saving writes ciphertext.
     */
    private function reencryptExistingRows(): void
    {
        PaymentTransaction::query()
            ->whereNotNull('payload')
            ->chunkById(100, function ($payments): void {
                foreach ($payments as $payment) {
                    // A row already in encrypted form would fail to decrypt and
                    // surface as null; skip rather than overwrite it.
                    if (! is_array($payment->payload)) {
                        continue;
                    }

                    $payment->save();
                }
            });
    }

    private function decryptAllRowsToPlaintext(): void
    {
        DB::table('payment_transactions')
            ->whereNotNull('payload')
            ->orderBy('id')
            ->chunk(100, function ($rows): void {
                foreach ($rows as $row) {
                    $payment = PaymentTransaction::query()->find($row->id);

                    if (! $payment || ! is_array($payment->payload)) {
                        continue;
                    }

                    // Write raw JSON past the model cast so the down migration
                    // leaves plaintext rather than re-encrypting.
                    DB::table('payment_transactions')
                        ->where('id', $row->id)
                        ->update(['payload' => json_encode($payment->payload)]);
                }
            });
    }

    private function migrateSqlite(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement('CREATE TABLE payment_transactions_encrypted_temp (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            wear_order_id INTEGER NOT NULL,
            user_id INTEGER,
            provider VARCHAR(40) NOT NULL,
            provider_reference VARCHAR(120),
            provider_transid VARCHAR(120),
            idempotency_key VARCHAR(100) NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            refunded_amount DECIMAL(12,2),
            currency VARCHAR(3) NOT NULL DEFAULT \'TZS\',
            status VARCHAR(30) NOT NULL,
            payload TEXT,
            initiated_at DATETIME,
            completed_at DATETIME,
            failed_at DATETIME,
            created_at DATETIME,
            updated_at DATETIME
        )');

        DB::statement('INSERT INTO payment_transactions_encrypted_temp
            SELECT id, wear_order_id, user_id, provider, provider_reference, provider_transid,
                   idempotency_key, amount, refunded_amount, currency, status, payload,
                   initiated_at, completed_at, failed_at, created_at, updated_at
            FROM payment_transactions');

        DB::statement('DROP TABLE payment_transactions');
        DB::statement('ALTER TABLE payment_transactions_encrypted_temp RENAME TO payment_transactions');

        DB::statement('CREATE INDEX payment_transactions_wear_order_id_status_index ON payment_transactions (wear_order_id, status)');
        DB::statement('CREATE UNIQUE INDEX payment_transactions_idempotency_key_unique ON payment_transactions (idempotency_key)');
        DB::statement('CREATE INDEX payment_transactions_provider_reference_index ON payment_transactions (provider_reference)');
        DB::statement('CREATE INDEX payment_transactions_status_index ON payment_transactions (status)');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    private function revertSqlite(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement('CREATE TABLE payment_transactions_plain_temp (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            wear_order_id INTEGER NOT NULL,
            user_id INTEGER,
            provider VARCHAR(40) NOT NULL,
            provider_reference VARCHAR(120),
            provider_transid VARCHAR(120),
            idempotency_key VARCHAR(100) NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            refunded_amount DECIMAL(12,2),
            currency VARCHAR(3) NOT NULL DEFAULT \'TZS\',
            status VARCHAR(30) NOT NULL,
            payload TEXT,
            initiated_at DATETIME,
            completed_at DATETIME,
            failed_at DATETIME,
            created_at DATETIME,
            updated_at DATETIME
        )');

        DB::statement('INSERT INTO payment_transactions_plain_temp
            SELECT id, wear_order_id, user_id, provider, provider_reference, provider_transid,
                   idempotency_key, amount, refunded_amount, currency, status, payload,
                   initiated_at, completed_at, failed_at, created_at, updated_at
            FROM payment_transactions');

        DB::statement('DROP TABLE payment_transactions');
        DB::statement('ALTER TABLE payment_transactions_plain_temp RENAME TO payment_transactions');

        DB::statement('CREATE INDEX payment_transactions_wear_order_id_status_index ON payment_transactions (wear_order_id, status)');
        DB::statement('CREATE UNIQUE INDEX payment_transactions_idempotency_key_unique ON payment_transactions (idempotency_key)');
        DB::statement('CREATE INDEX payment_transactions_provider_reference_index ON payment_transactions (provider_reference)');
        DB::statement('CREATE INDEX payment_transactions_status_index ON payment_transactions (status)');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
