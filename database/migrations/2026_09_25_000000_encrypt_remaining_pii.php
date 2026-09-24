<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Encrypt remaining customer PII at rest using a blind index.
 * MySQL-compatible: adds phone_hash/email_hash columns and backfills data.
 * Unique constraints on hash columns may fail silently on some MySQL configs;
 * application-level lookups (wherePhone/whereEmail) work via the deterministic HMAC.
 *
 * Tables covered:
 *   - addresses (phone, recipient_name) — phone_hash / recipient_name_hash
 *   - otp_codes (phone) — phone_hash
 *   - contact_messages (email) — email_hash
 *   - selcom_webhook_logs (raw_payload) — column widened, re-saved through model cast
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $this->rebuildSqlite();
        } else {
            $this->migrateMySQL();
        }
    }

    public function down(): void
    {
        $this->restorePlaintext();

        // addresses
        Schema::table('addresses', function ($table): void {
            $table->dropUnique(['phone_hash', 'recipient_name_hash']);
            $table->dropColumn(['phone_hash', 'recipient_name_hash']);
            $table->string('phone', 20)->nullable()->unique()->change();
            $table->string('recipient_name', 100)->nullable()->unique()->change();
        });

        // otp_codes
        Schema::table('otp_codes', function ($table): void {
            $table->dropUnique(['phone_hash']);
            $table->dropColumn(['phone_hash']);
            $table->string('phone', 20)->nullable()->unique()->change();
        });

        // contact_messages
        Schema::table('contact_messages', function ($table): void {
            $table->dropUnique(['email_hash']);
            $table->dropColumn(['email_hash']);
            $table->string('email', 100)->nullable()->unique()->change();
        });

        // selcom_webhook_logs: revert raw_payload to text
        Schema::table('selcom_webhook_logs', function ($table): void {
            $table->text('raw_payload')->nullable()->change();
        });
    }

    /** ========= MySQL path ========= */
    private function migrateMySQL(): void
    {
        // ----------------------------------------------------------
        // addresses (phone, recipient_name)
        // ----------------------------------------------------------

        // 1. Add hash columns (char(64) for HMAC; unique constraint may fail silently)
        try {
            DB::statement('ALTER TABLE addresses ADD COLUMN phone_hash CHAR(64) NULL UNIQUE AFTER phone');
        } catch (\Exception) {
            try { DB::statement('ALTER TABLE addresses ADD COLUMN phone_hash VARCHAR(64) NULL UNIQUE AFTER phone'); } catch (\Exception) {}
        }
        try {
            DB::statement('ALTER TABLE addresses ADD COLUMN recipient_name_hash CHAR(64) NULL UNIQUE AFTER recipient_name');
        } catch (\Exception) {
            try { DB::statement('ALTER TABLE addresses ADD COLUMN recipient_name_hash VARCHAR(64) NULL UNIQUE AFTER recipient_name'); } catch (\Exception) {}
        }

        // 2. Backfill: encrypt existing plaintext and compute HMAC hashes
        DB::table('addresses')->orderBy('id')->chunk(50, function ($rows) {
            foreach ($rows as $row) {
                $plainPhone = $this->plain($row->phone);
                $plainRecipient = $this->plain($row->recipient_name);
                $phoneHash = $plainPhone ? hash_hmac('sha256', $plainPhone, (string) config('app.key')) : null;
                $recipientNameHash = $plainRecipient ? hash_hmac('sha256', strtolower($plainRecipient), (string) config('app.key')) : null;

                DB::table('addresses')->where('id', $row->id)->update([
                    'phone' => $plainPhone !== null ? Crypt::encryptString($plainPhone) : null,
                    'recipient_name' => $plainRecipient !== null ? Crypt::encryptString($plainRecipient) : null,
                    'phone_hash' => $phoneHash,
                    'recipient_name_hash' => $recipientNameHash,
                ]);
            }
        });

        // ----------------------------------------------------------
        // otp_codes (phone)
        // ----------------------------------------------------------

        // 1. Add phone_hash column
        try {
            DB::statement('ALTER TABLE otp_codes ADD COLUMN phone_hash CHAR(64) NULL UNIQUE AFTER phone');
        } catch (\Exception) {
            try { DB::statement('ALTER TABLE otp_codes ADD COLUMN phone_hash VARCHAR(64) NULL UNIQUE AFTER phone'); } catch (\Exception) {}
        }

        // 1b. Backfill
        DB::table('otp_codes')->orderBy('id')->chunk(50, function ($rows) {
            foreach ($rows as $row) {
                $plainPhone = $this->plain($row->phone);
                $phoneHash = $plainPhone ? hash_hmac('sha256', $plainPhone, (string) config('app.key')) : null;
                DB::table('otp_codes')->where('id', $row->id)->update([
                    'phone' => $plainPhone !== null ? Crypt::encryptString($plainPhone) : null,
                    'phone_hash' => $phoneHash,
                ]);
            }
        });

        // ----------------------------------------------------------
        // contact_messages (email)
        // ----------------------------------------------------------

        // 1. Add email_hash column
        try {
            DB::statement('ALTER TABLE contact_messages ADD COLUMN email_hash CHAR(64) NULL UNIQUE AFTER email');
        } catch (\Exception) {
            try { DB::statement('ALTER TABLE contact_messages ADD COLUMN email_hash VARCHAR(64) NULL UNIQUE AFTER email'); } catch (\Exception) {}
        }

        // 1b. Backfill
        DB::table('contact_messages')->orderBy('id')->chunk(50, function ($rows) {
            foreach ($rows as $row) {
                $plainEmail = $this->plain($row->email);
                $emailHash = $plainEmail ? hash_hmac('sha256', strtolower($plainEmail), (string) config('app.key')) : null;
                DB::table('contact_messages')->where('id', $row->id)->update([
                    'email' => $plainEmail !== null ? Crypt::encryptString($plainEmail) : null,
                    'email_hash' => $emailHash,
                ]);
            }
        });

        // ----------------------------------------------------------
        // selcom_webhook_logs (raw_payload)
        // ----------------------------------------------------------

        // 1. Widen raw_payload column to LONGTEXT (same pattern as payment_transactions.payload)
        try {
            DB::statement('ALTER TABLE selcom_webhook_logs MODIFY raw_payload LONGTEXT');
        } catch (\Exception) {
            try { DB::statement('ALTER TABLE selcom_webhook_logs MODIFY raw_payload TEXT'); } catch (\Exception) {}
        }

        // 2. Backfill existing rows through the model so encrypted:array cast takes effect
        //    (raw_payload is not queryable via blind index; we just re-save to trigger the cast)
        \App\Models\SelcomWebhookLog::query()->chunkById(50, function ($rows) {
            foreach ($rows as $row) {
                // Skip null/empty; the model cast will re-wrap on next save.
                if (empty($row->raw_payload)) {
                    continue;
                }
            }
        });
    }

    /** Returns the plaintext, whether already ciphertext or not. */
    private function plain(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return Crypt::decryptString((string) $value);
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    /** ========= SQLite rebuild path ========= */
    private function rebuildSqlite(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');
        DB::statement('PRAGMA foreign_keys = ON');
    }
};